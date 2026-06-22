<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case WaitingOtherParty = 'waiting_other_party';
    case PendingPublish = 'pending_publish';
    case Pending = 'pending';
    case Published = 'published';
    case Hidden = 'hidden';
    case Flagged = 'flagged';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case RemovedFuture = 'removed_future';
    case DisputedFuture = 'disputed_future';
    case Closed = 'closed';

    public function label(): string
    {
        return __('statuses.review.'.$this->value);
    }
}
