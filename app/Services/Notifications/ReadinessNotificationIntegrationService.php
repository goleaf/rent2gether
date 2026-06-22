<?php

namespace App\Services\Notifications;

class ReadinessNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyPlaceReady(mixed $readiness): void
    {
        $this->notifyHost($this->bookingFrom($readiness), 'place_ready');
    }
}
