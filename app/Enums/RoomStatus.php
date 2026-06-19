<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Active = 'active';
    case Hidden = 'hidden';
    case Maintenance = 'maintenance';
    case Closed = 'closed';
}
