<?php

namespace App\Enums;

enum BookingPaymentMode: string
{
    case AwaitingPayment = 'awaiting_payment';
    case WithDeposit = 'with_deposit';
    case WithoutDeposit = 'without_deposit';
    case PartialPayment = 'partial_payment';
    case FullPayment = 'full_payment';

    public function label(): string
    {
        return __('statuses.booking_payment_mode.'.$this->value);
    }
}
