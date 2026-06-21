<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\User;

class BookingCancellationPrivacyService
{
    public function canGuestView(User $guest, BookingCancellation $cancellation): bool
    {
        return (int) $cancellation->guest_user_id === (int) $guest->id
            || (int) $cancellation->booking?->guest_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, BookingCancellation $cancellation): bool
    {
        return (int) $cancellation->host_user_id === (int) $host->id
            || (int) $cancellation->booking?->host_user_id === (int) $host->id;
    }

    public function canGuestCreate(User $guest, Booking $booking): bool
    {
        return (int) $booking->guest_user_id === (int) $guest->id
            && ! $this->isClosed($booking);
    }

    public function canHostCreate(User $host, Booking $booking): bool
    {
        return (int) $booking->host_user_id === (int) $host->id
            && ! $this->isClosed($booking);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingCancellation $cancellation): array
    {
        abort_unless($this->canGuestView($guest, $cancellation), 403);

        return $cancellation->only([
            'cancellation_number',
            'booking_id',
            'cancelled_by_type',
            'cancellation_type',
            'reason_key',
            'status',
            'check_in_date',
            'check_out_date',
            'cancelled_at',
            'nights_used',
            'nights_unused',
            'accommodation_refund_amount',
            'cleaning_fee_refund_amount',
            'service_fee_refund_amount',
            'deposit_refund_amount',
            'penalty_amount',
            'total_refund_amount',
            'total_non_refundable_amount',
            'currency',
            'refund_status',
            'calendar_release_status',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingCancellation $cancellation): array
    {
        abort_unless($this->canHostView($host, $cancellation), 403);

        return $cancellation->makeVisible(['host_payout_adjustment_amount'])->toArray();
    }

    private function isClosed(Booking $booking): bool
    {
        $status = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;

        return in_array($status, ['closed', 'completed', 'cancelled_by_guest', 'cancelled_by_host', 'cancelled_guest', 'cancelled_host'], true);
    }
}
