<?php

namespace App\Enums;

enum PropertyRentalUnitType: string
{
    case WholeProperty = 'whole_property';
    case Room = 'room';
    case SleepingPlace = 'sleeping_place';
    case SeveralSleepingPlaces = 'several_sleeping_places';

    public function label(): string
    {
        return __('statuses.property_rental_unit_type.'.$this->value);
    }
}
