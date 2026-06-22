<?php

namespace App\Services\Notifications;

class InventoryNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyInventoryIssue(mixed $issue): void
    {
        $this->notifyHost($this->bookingFrom($issue), 'maintenance_reported');
    }
}
