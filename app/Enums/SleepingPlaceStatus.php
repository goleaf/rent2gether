<?php

namespace App\Enums;

enum SleepingPlaceStatus: string
{
    case Active = 'active';
    case Hidden = 'hidden';
    case Maintenance = 'maintenance';
    case Closed = 'closed';
}
