<?php

namespace App\Services\Notifications;

class StayNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyStayStarted(mixed $stay): void
    {
        $this->notifyGuest($this->bookingFrom($stay), 'booking_confirmed');
    }
}
