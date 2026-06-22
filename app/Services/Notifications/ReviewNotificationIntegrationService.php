<?php

namespace App\Services\Notifications;

class ReviewNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyReviewRequest(mixed $booking): void
    {
        $this->notifyGuest($this->bookingFrom($booking), 'booking_confirmed', ['notification_type' => 'reminder']);
    }
}
