<?php

namespace App\Services\Notifications;

class NoShowNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyNoShowReported(mixed $noShow): void
    {
        $this->notifyGuest($this->bookingFrom($noShow), 'no_show_reported', ['priority' => 'urgent']);
    }
}
