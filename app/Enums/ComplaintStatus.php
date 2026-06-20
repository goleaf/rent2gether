<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case Created = 'created';
    case WaitingForOtherSide = 'waiting_for_other_side';
    case NeedsMoreInfo = 'needs_more_info';
    case UnderReviewBySystem = 'under_review_by_system';
    case Open = 'open';
    case AwaitingResponse = 'awaiting_response';
    case Investigating = 'investigating';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return __('statuses.complaint.'.$this->value);
    }
}
