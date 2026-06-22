<?php

namespace App\Services\Notifications;

class CleaningNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyCleaningDue(mixed $task): void
    {
        $this->notifyHost($this->bookingFrom($task), 'cleaning_due');
    }
}
