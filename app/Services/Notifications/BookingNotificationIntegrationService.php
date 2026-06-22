<?php

namespace App\Services\Notifications;

use App\Models\Booking;

class BookingNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyBookingStarted(Booking $booking): void
    {
        $this->notifyGuest($booking, 'booking_started');
    }

    public function notifyBookingConfirmed(Booking $booking): void
    {
        $this->notifyGuest($booking, 'booking_confirmed');
        $this->notifyHost($booking, 'booking_confirmed');
    }

    public function notifyBookingRejected(Booking $booking): void
    {
        $this->notifyGuest($booking, 'booking_rejected');
    }

    public function notifyBookingCancelled(Booking $booking): void
    {
        $this->notifyGuest($booking, 'cancellation_created');
        $this->notifyHost($booking, 'cancellation_created');
    }
}
