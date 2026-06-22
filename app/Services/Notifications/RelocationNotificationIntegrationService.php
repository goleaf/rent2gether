<?php

namespace App\Services\Notifications;

class RelocationNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyRelocationRequested(mixed $relocation): void
    {
        $this->notifyHost($this->bookingFrom($relocation), 'relocation_requested');
    }
}
