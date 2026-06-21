<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case AwaitingPayment = 'awaiting_payment';
    case WaitingPayment = 'waiting_payment';
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
    case RefundedPartial = 'refunded_partial';
    case RefundedFull = 'refunded_full';
    case Reversed = 'reversed';

    public function label(): string
    {
        return __('statuses.payment.'.$this->value);
    }
}
