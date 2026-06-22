<?php

namespace App\Services\Notifications;

class ExtensionNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyExtensionRequested(mixed $extension): void
    {
        $this->notifyHost($this->bookingFrom($extension), 'extension_requested');
    }
}
