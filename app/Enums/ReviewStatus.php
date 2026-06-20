<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Hidden = 'hidden';
    case Flagged = 'flagged';

    public function label(): string
    {
        return __('statuses.review.'.$this->value);
    }
}
