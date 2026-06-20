<?php

namespace App\Enums;

enum RoomType: string
{
    case Shared = 'shared';
    case Private = 'private';
    case Dormitory = 'dormitory';
    case StudioRoom = 'studio_room';

    public function label(): string
    {
        return __('statuses.room_type.'.$this->value);
    }
}
