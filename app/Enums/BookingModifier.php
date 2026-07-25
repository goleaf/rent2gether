<?php

namespace App\Enums;

enum BookingModifier: string
{
    case Extension = 'extension';
    case Relocation = 'relocation';
    case GroupBooking = 'group_booking';
    case TwoGuestSleepingPlace = 'two_guest_sleeping_place';

    public function label(): string
    {
        return __('statuses.booking_modifier.'.$this->value);
    }
}
