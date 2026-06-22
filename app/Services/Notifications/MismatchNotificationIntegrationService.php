<?php

namespace App\Services\Notifications;

class MismatchNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyMismatchOpened(mixed $report): void
    {
        $this->notifyHost($this->bookingFrom($report), 'complaint_opened');
    }
}
