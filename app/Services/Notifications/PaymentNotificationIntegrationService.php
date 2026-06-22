<?php

namespace App\Services\Notifications;

class PaymentNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyPaymentRequired(mixed $payment): void
    {
        $this->notifyGuest($this->bookingFrom($payment), 'payment_required');
    }

    public function notifyPaymentCompleted(mixed $payment): void
    {
        $booking = $this->bookingFrom($payment);
        $this->notifyGuest($booking, 'booking_confirmed');
        $this->notifyHost($booking, 'booking_confirmed');
    }

    public function notifyPaymentFailed(mixed $payment): void
    {
        $this->notifyGuest($this->bookingFrom($payment), 'payment_required');
    }

    public function notifyRefundCreated(mixed $refund): void
    {
        $this->notifyGuest($this->bookingFrom($refund), 'cancellation_created');
    }

    public function notifyRefundCompleted(mixed $refund): void
    {
        $this->notifyGuest($this->bookingFrom($refund), 'deposit_returned');
    }
}
