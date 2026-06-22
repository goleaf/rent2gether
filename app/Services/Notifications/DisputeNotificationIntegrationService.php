<?php

namespace App\Services\Notifications;

class DisputeNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyDisputeOpened(mixed $case): void
    {
        $booking = $this->bookingFrom($case);
        $this->notifyGuest($booking, 'dispute_opened');
        $this->notifyHost($booking, 'dispute_opened');
    }
}
