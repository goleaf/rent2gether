<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Failed = 'failed';
    case RefundedPartial = 'refunded_partial';
    case RefundedFull = 'refunded_full';
    case Reversed = 'reversed';

    public function label(): string
    {
        return __('statuses.payment.'.$this->value);
    }
}
