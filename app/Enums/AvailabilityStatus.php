<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Available = 'available';
    case Blocked = 'blocked';
    case Maintenance = 'maintenance';
    case Cleaning = 'cleaning';
}
