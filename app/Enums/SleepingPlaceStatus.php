<?php

namespace App\Enums;

enum SleepingPlaceStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Hidden = 'hidden';
    case Unavailable = 'unavailable';
    case Repair = 'repair';
    case Maintenance = 'maintenance';
    case Closed = 'closed';

    public function label(): string
    {
        return __('statuses.sleeping_place.'.$this->value);
    }
}
