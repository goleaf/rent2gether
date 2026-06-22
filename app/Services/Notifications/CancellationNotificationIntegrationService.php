<?php

namespace App\Services\Notifications;

class CancellationNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyCancellationCreated(mixed $cancellation): void
    {
        $booking = $this->bookingFrom($cancellation);
        $this->notifyGuest($booking, 'cancellation_created');
        $this->notifyHost($booking, 'cancellation_created');
    }
}
