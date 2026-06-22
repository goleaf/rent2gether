<?php

namespace App\Services\Notifications;

class HostUnresponsiveNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyHostUnresponsiveReported(mixed $case): void
    {
        $this->notifyHost($this->bookingFrom($case), 'host_unresponsive_reported', ['priority' => 'critical']);
    }
}
