<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\User;

class HostUnresponsiveStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(BookingHostUnresponsiveCase $case, string $newStatus, ?User $user = null, array $context = []): BookingHostUnresponsiveCase
    {
        $oldStatus = (string) $case->status;

        if ($oldStatus === $newStatus) {
            return $case;
        }

        if (! $this->canTransition($case, $newStatus)) {
            abort(422, __('host_unresponsive.validation.invalid_status_transition'));
        }

        $case->forceFill([
            ...$this->timestampAttributes($newStatus),
            'status' => $newStatus,
        ])->save();

        $case->statusLogs()->create([
            'booking_id' => $case->booking_id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $case->fresh();
    }

    public function canTransition(BookingHostUnresponsiveCase $case, string $newStatus): bool
    {
        if ((string) $case->status === $newStatus) {
            return true;
        }

        return ! in_array((string) $case->status, ['closed', 'cancelled'], true);
    }

    public function syncBookingStatus(BookingHostUnresponsiveCase $case): Booking
    {
        $booking = $case->booking()->firstOrFail();

        if ($case->decision_key === 'confirmed_host_unresponsive' || $case->status === 'unresolved') {
            $booking->forceFill(['status' => BookingStatus::HostUnresponsive])->save();
        }

        if ($case->status === 'dispute_opened') {
            $booking->forceFill([
                'status' => BookingStatus::DisputeOpened,
                'has_dispute' => true,
            ])->save();
        }

        return $booking->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function timestampAttributes(string $newStatus): array
    {
        return match ($newStatus) {
            'unresolved', 'resolved', 'access_resolved', 'dispute_opened' => ['decision_at' => now()],
            'closed' => ['closed_at' => now()],
            default => [],
        };
    }
}
