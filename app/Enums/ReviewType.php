<?php

namespace App\Enums;

enum ReviewType: string
{
    case GuestToPlace = 'guest_to_place';
    case HostToGuest = 'host_to_guest';
}
