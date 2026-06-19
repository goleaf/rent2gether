<?php

namespace App\Enums;

enum PropertyType: string
{
    case Apartment = 'apartment';
    case House = 'house';
    case Studio = 'studio';
    case Hostel = 'hostel';
    case GuestHouse = 'guesthouse';
    case Dormitory = 'dormitory';
    case Cottage = 'cottage';
    case Other = 'other';

    public function label(): string
    {
        return __('statuses.property_type.'.$this->value);
    }
}
