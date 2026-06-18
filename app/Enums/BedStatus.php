<?php

namespace App\Enums;

enum BedStatus: string
{
    case Active = 'active';
    case Hidden = 'hidden';
    case Maintenance = 'maintenance';
    case Closed = 'closed';
}
