<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\SleepingPlaceBookingDateLock;

class BookingPaymentCalendarIntegrationService
{
    public function convertPaymentLocksToBooked(Booking $booking): void
    {
        SleepingPlaceBookingDateLock::query()
            ->where('booking_id', $booking->id)
            ->where('status', 'active')
            ->where('lock_type', 'payment_pending')
            ->update([
                'lock_type' => 'booked',
                'expires_at' => null,
                'updated_at' => now(),
            ]);
    }

    public function releaseLocksAfterPaymentFailure(Booking $booking): void
    {
        $this->releaseActiveLocks($booking, 'released');
    }

    public function releaseLocksAfterPaymentExpiration(Booking $booking): void
    {
        $this->releaseActiveLocks($booking, 'expired');
    }

    public function syncAvailabilityAfterPayment(Booking $booking): void
    {
        $this->convertPaymentLocksToBooked($booking);
    }

    private function releaseActiveLocks(Booking $booking, string $status): void
    {
        SleepingPlaceBookingDateLock::query()
            ->where('booking_id', $booking->id)
            ->where('status', 'active')
            ->update([
                'status' => $status,
                'released_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
