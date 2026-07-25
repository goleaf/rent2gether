<?php

namespace App\Enums;

enum PropertyType: string
{
    case Apartment = 'apartment';
    case House = 'house';
    case Studio = 'studio';
    case RoomInApartment = 'room_in_apartment';
    case RoomInHouse = 'room_in_house';
    case Dormitory = 'dormitory';
    case Hostel = 'hostel';
    case GuestHouse = 'guesthouse';
    case Cottage = 'cottage';
    case Dacha = 'dacha';
    case Apartments = 'apartments';
    case MiniHotel = 'mini_hotel';
    case CapsuleHousing = 'capsule_housing';
    case CommercialLivingSpace = 'commercial_living_space';
    case Other = 'other';

    public function label(): string
    {
        return __('statuses.property_type.'.$this->value);
    }
}
