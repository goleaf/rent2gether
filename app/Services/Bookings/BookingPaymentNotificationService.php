<?php

namespace App\Services\Bookings;

use App\Models\BookingPayment;
use App\Models\BookingRefund;

class BookingPaymentNotificationService
{
    public function notifyGuestPaymentRequired(BookingPayment $payment): void
    {
        $this->storeNotificationIntent($payment, 'payment_required');
    }

    public function notifyGuestPaymentSucceeded(BookingPayment $payment): void
    {
        $this->storeNotificationIntent($payment, 'payment_succeeded');
    }

    public function notifyGuestPaymentFailed(BookingPayment $payment): void
    {
        $this->storeNotificationIntent($payment, 'payment_failed');
    }

    public function notifyGuestPaymentDeadlineSoon(BookingPayment $payment): void
    {
        $this->storeNotificationIntent($payment, 'payment_deadline_soon');
    }

    public function notifyGuestPaymentExpired(BookingPayment $payment): void
    {
        $this->storeNotificationIntent($payment, 'payment_expired');
    }

    public function notifyHostGuestPaid(BookingPayment $payment): void
    {
        $this->storeNotificationIntent($payment, 'host_guest_paid');
    }

    public function notifyHostGuestPaymentExpired(BookingPayment $payment): void
    {
        $this->storeNotificationIntent($payment, 'host_guest_payment_expired');
    }

    public function notifyRefundCreated(BookingRefund $refund): void
    {
        $this->storeRefundNotificationIntent($refund, 'refund_created');
    }

    public function notifyRefundCompleted(BookingRefund $refund): void
    {
        $this->storeRefundNotificationIntent($refund, 'refund_completed');
    }

    private function storeNotificationIntent(BookingPayment $payment, string $eventKey): void
    {
        $payment->statusLogs()->create([
            'booking_id' => $payment->booking_id,
            'new_status' => $payment->status,
            'event_key' => $eventKey,
        ]);
    }

    private function storeRefundNotificationIntent(BookingRefund $refund, string $eventKey): void
    {
        $refund->bookingPayment?->statusLogs()->create([
            'booking_refund_id' => $refund->id,
            'booking_id' => $refund->booking_id,
            'new_status' => $refund->status,
            'event_key' => $eventKey,
        ]);
    }
}
