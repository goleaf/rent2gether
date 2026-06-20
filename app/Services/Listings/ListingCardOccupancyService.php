<?php

namespace App\Services\Listings;

use App\Data\Listings\ListingCardOccupancyData;
use App\Models\SleepingPlace;

class ListingCardOccupancyService
{
    public function getOccupancy(SleepingPlace $place): ListingCardOccupancyData
    {
        $room = $place->room;
        $roomPlaces = max(1, (int) ($room?->beds_count ?: $room?->max_guests ?: $room?->capacity ?: 1));
        $occupied = max(0, (int) ($room?->occupied_places_count ?: 0));
        $available = max(0, (int) ($room?->available_places_count ?: ($roomPlaces - $occupied)));
        $propertyPeople = max(0, (int) ($place->property?->current_guests_count ?: $place->property?->current_residents_count ?: 0));

        return new ListingCardOccupancyData(
            roomPlacesCount: $roomPlaces,
            roomAvailablePlacesCount: $available,
            roomOccupiedPlacesCount: $occupied,
            peopleInRoomSummary: $occupied > 0
                ? trans_choice('listing_card.people_in_room', $occupied, ['count' => $occupied])
                : __('listing_card.people_in_room_empty'),
            peopleInPropertyCount: $propertyPeople,
        );
    }
}
