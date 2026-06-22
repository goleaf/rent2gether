<?php

namespace App\Services\Notifications;

class InspectionNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyInspectionDue(mixed $task): void
    {
        $this->notifyHost($this->bookingFrom($task), 'inspection_due');
    }
}
