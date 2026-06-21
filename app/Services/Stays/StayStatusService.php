<?php

namespace App\Services\Stays;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingStay;
use App\Models\User;
use App\Services\Bookings\BookingStatusService;
use Illuminate\Validation\ValidationException;

class StayStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(BookingStay $stay, string $newStatus, ?User $user = null, array $context = []): BookingStay
    {
        $oldStatus = (string) $stay->status;

        if ($oldStatus === $newStatus) {
            return $stay;
        }

        if (! $this->canTransition($stay, $newStatus)) {
            throw ValidationException::withMessages([
                'status' => __('stays.validation.invalid_status_transition'),
            ]);
        }

        $stay->forceFill([
            ...$this->timestampAttributes($newStatus),
            ...$this->flagAttributes($newStatus),
            'status' => $newStatus,
        ])->save();

        $stay->statusLogs()->create([
            'booking_id' => $stay->booking_id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? 'stays.events.'.$this->eventKeyForStatus($newStatus),
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        app(StayEventService::class)->record($stay->refresh(), $this->eventKeyForStatus($newStatus), [
            'event_type' => $context['event_type'] ?? ($user ? 'host_action' : 'system'),
            'user_id' => $user?->id,
        ]);

        $this->syncBookingStatus($stay->refresh());

        return $stay->refresh();
    }

    public function canTransition(BookingStay $stay, string $newStatus): bool
    {
        if ((string) $stay->status === $newStatus) {
            return true;
        }

        $terminal = ['closed'];

        return ! in_array((string) $stay->status, $terminal, true);
    }

    public function syncBookingStatus(BookingStay $stay): Booking
    {
        $booking = $stay->booking()->firstOrFail();
        $statuses = app(BookingStatusService::class);

        $target = match ((string) $stay->status) {
            'active' => BookingStatus::StayInProgress->value,
            'checkout_soon' => BookingStatus::CheckOutSoon->value,
            'guest_checked_out' => BookingStatus::GuestCheckedOut->value,
            'waiting_inspection' => BookingStatus::WaitingPropertyInspection->value,
            'completed' => BookingStatus::Completed->value,
            'closed' => BookingStatus::Closed->value,
            'disputed' => BookingStatus::DisputeOpened->value,
            'problem_reported' => BookingStatus::ProblemReported->value,
            default => null,
        };

        if ($target !== null && $statuses->canTransition($booking, $target)) {
            return $statuses->transition($booking, $target, null, [
                'reason_key' => 'stays.events.'.$this->eventKeyForStatus((string) $stay->status),
                'event_key' => $this->eventKeyForStatus((string) $stay->status),
                'event_type' => 'system',
            ]);
        }

        return $booking->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function timestampAttributes(string $newStatus): array
    {
        return match ($newStatus) {
            'active' => ['started_at' => now()],
            'guest_checked_out' => ['actual_check_out_at' => now(), 'ended_at' => now()],
            'completed' => ['ended_at' => now()],
            'closed' => ['closed_at' => now()],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function flagAttributes(string $newStatus): array
    {
        return match ($newStatus) {
            'checkout_soon' => ['checkout_soon' => true],
            'extension_requested' => ['extension_requested' => true],
            'relocation_requested' => ['relocation_requested' => true],
            'problem_reported' => ['has_neighbor_problem' => true],
            'completed', 'closed' => ['checkout_soon' => false, 'checkout_required' => false],
            default => [],
        };
    }

    private function eventKeyForStatus(string $status): string
    {
        return match ($status) {
            'active' => 'stay_started',
            'extension_requested' => 'extension_requested',
            'extension_approved' => 'extension_approved',
            'relocation_requested' => 'relocation_requested',
            'relocation_scheduled' => 'relocation_scheduled',
            'checkout_soon' => 'checkout_soon',
            'checkout_started' => 'checkout_started',
            'guest_checked_out' => 'guest_checked_out',
            'completed' => 'stay_completed',
            'closed' => 'stay_closed',
            'problem_reported' => 'neighbor_problem_reported',
            'disputed' => 'complaint_opened',
            default => 'guest_present',
        };
    }
}
