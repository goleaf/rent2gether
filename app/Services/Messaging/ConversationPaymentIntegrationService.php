<?php

namespace App\Services\Messaging;

use App\Models\BookingPayment;
use App\Models\BookingRefund;

class ConversationPaymentIntegrationService
{
    public function addPaymentRequiredEvent(BookingPayment $payment): void
    {
        $this->add($payment, 'payment_required');
    }

    public function addPaymentCompletedEvent(BookingPayment $payment): void
    {
        $this->add($payment, 'payment_completed');
    }

    public function addPaymentFailedEvent(BookingPayment $payment): void
    {
        $this->add($payment, 'payment_failed', 'important');
    }

    public function addRefundCreatedEvent(BookingRefund $refund): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($refund->booking()->firstOrFail(), 'refund_created', [
            'source_type' => 'refund',
            'source_id' => $refund->id,
        ]);
    }

    private function add(BookingPayment $payment, string $eventKey, string $importance = 'normal'): void
    {
        app(ConversationSystemEventService::class)->addBookingEvent($payment->booking()->firstOrFail(), $eventKey, [
            'source_type' => 'payment',
            'source_id' => $payment->id,
            'importance_level' => $importance,
        ]);
    }
}
