<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case Open = 'open';
    case AwaitingResponse = 'awaiting_response';
    case Investigating = 'investigating';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return __('statuses.complaint.'.$this->value);
    }
}
