<?php

namespace App\Services\Notifications;

class ComplaintNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyComplaintOpened(mixed $case): void
    {
        $this->notifyHost($this->bookingFrom($case), 'complaint_opened');
    }
}
