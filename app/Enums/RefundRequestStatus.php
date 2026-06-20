<?php

namespace App\Enums;

enum RefundRequestStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';

    public function label(): string
    {
        return __('statuses.refund_request.'.$this->value);
    }
}
