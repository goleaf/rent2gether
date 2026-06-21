<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\User;

class BookingCancellationStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(BookingCancellation $cancellation, string $newStatus, ?User $user = null, array $context = []): BookingCancellation
    {
        $oldStatus = $cancellation->status;

        $cancellation->forceFill([
            'status' => $newStatus,
            'completed_at' => in_array($newStatus, ['booking_cancelled', 'refund_completed', 'closed'], true) ? now() : $cancellation->completed_at,
        ])->save();

        $cancellation->statusLogs()->create([
            'booking_id' => $cancellation->booking_id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $cancellation->fresh();
    }

    public function canTransition(BookingCancellation $cancellation, string $newStatus): bool
    {
        return ! in_array($cancellation->status, ['closed', 'cancelled', 'rejected'], true)
            || $newStatus === 'closed';
    }

    public function syncBookingStatus(BookingCancellation $cancellation): Booking
    {
        $cancellation->loadMissing('booking');

        return $cancellation->booking;
    }
}
