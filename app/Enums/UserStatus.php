<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Restricted = 'restricted';
    case Suspended = 'suspended';
    case Blocked = 'blocked';

    public function label(): string
    {
        return __('statuses.user.'.$this->value);
    }
}
