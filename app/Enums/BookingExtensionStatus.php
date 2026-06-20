<?php

namespace App\Enums;

enum BookingExtensionStatus: string
{
    case Draft = 'draft';
    case AwaitingHostApproval = 'awaiting_host_approval';
    case AwaitingPayment = 'awaiting_payment';
    case Approved = 'approved';
    case Declined = 'declined';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('statuses.extension.'.$this->value);
    }
}
