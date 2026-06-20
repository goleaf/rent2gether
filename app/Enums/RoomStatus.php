<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Hidden = 'hidden';
    case Unavailable = 'unavailable';
    case Maintenance = 'maintenance';
    case Closed = 'closed';

    public function label(): string
    {
        return __('statuses.room.'.$this->value);
    }
}
