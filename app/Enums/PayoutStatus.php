<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case OnHold = 'on_hold';

    public function label(): string
    {
        return __('statuses.payout.'.$this->value);
    }
}
