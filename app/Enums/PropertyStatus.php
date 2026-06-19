<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Hidden = 'hidden';
    case Suspended = 'suspended';

    public function label(): string
    {
        return __('statuses.property.'.$this->value);
    }
}
