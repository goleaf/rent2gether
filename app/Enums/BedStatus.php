<?php

namespace App\Enums;

enum BedStatus: string
{
    case Active = 'active';
    case Hidden = 'hidden';
    case Maintenance = 'maintenance';
    case Closed = 'closed';

    public function label(): string
    {
        return __('statuses.bed.'.$this->value);
    }
}
