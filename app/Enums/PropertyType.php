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
        return match ($this) {
            self::Apartment => 'Apartment',
            self::House => 'House',
            self::Studio => 'Studio',
            self::Hostel => 'Hostel',
            self::GuestHouse => 'Guest House',
            self::Dormitory => 'Dormitory',
            self::Cottage => 'Cottage',
            self::Other => 'Other',
        };
    }
}
