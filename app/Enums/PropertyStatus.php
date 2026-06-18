<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Hidden = 'hidden';
    case Suspended = 'suspended';
}
