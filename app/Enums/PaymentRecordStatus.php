<?php

namespace App\Enums;

enum PaymentRecordStatus: string
{
    case AwaitingPayment = 'awaiting_payment';
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case RefundedPartial = 'refunded_partial';
    case RefundedFull = 'refunded_full';

    public function label(): string
    {
        return __('statuses.payment_record.'.$this->value);
    }
}
