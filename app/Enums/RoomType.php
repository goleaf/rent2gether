<?php

namespace App\Enums;

enum RoomType: string
{
    case Shared = 'shared';
    case Private = 'private';
    case Dormitory = 'dormitory';
    case StudioRoom = 'studio_room';
}
