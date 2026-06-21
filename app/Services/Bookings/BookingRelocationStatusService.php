<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationStatusLog;
use App\Models\User;

class BookingRelocationStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(BookingRelocation $relocation, string $newStatus, ?User $user = null, array $context = []): BookingRelocation
    {
        $oldStatus = $relocation->status;
        $relocation->forceFill([
            'status' => $newStatus,
        ])->save();

        BookingRelocationStatusLog::query()->create([
            'booking_relocation_id' => $relocation->id,
            'original_booking_id' => $relocation->original_booking_id,
            'new_booking_id' => $relocation->new_booking_id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $relocation->refresh();
    }

    public function canTransition(BookingRelocation $relocation, string $newStatus): bool
    {
        $closed = ['applied', 'rejected', 'expired', 'cancelled_by_guest', 'cancelled_by_host', 'closed'];

        return ! in_array((string) $relocation->status, $closed, true) || $newStatus === 'closed';
    }

    public function syncBookingStatuses(BookingRelocation $relocation): void
    {
        $relocation->originalBooking?->statusHistories()->create([
            'from_status' => null,
            'to_status' => $relocation->status,
            'changed_by_user_id' => null,
            'note' => 'booking_relocations.status_synced',
        ]);
    }

    public function syncStayStatus(BookingRelocation $relocation): void
    {
        $relocation->bookingStay?->forceFill([
            'relocation_requested' => in_array((string) $relocation->status, ['requested', 'waiting_guest_consent', 'waiting_host_consent'], true),
            'status' => $relocation->status === 'applied' ? 'active' : 'relocation_requested',
        ])->save();
    }
}
