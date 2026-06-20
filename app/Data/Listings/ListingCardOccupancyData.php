<?php

namespace App\Data\Listings;

final readonly class ListingCardOccupancyData
{
    public function __construct(
        public int $roomPlacesCount,
        public int $roomAvailablePlacesCount,
        public int $roomOccupiedPlacesCount,
        public string $peopleInRoomSummary,
        public int $peopleInPropertyCount = 0,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'room_places_count' => $this->roomPlacesCount,
            'room_available_places_count' => $this->roomAvailablePlacesCount,
            'room_occupied_places_count' => $this->roomOccupiedPlacesCount,
            'people_in_room_summary' => $this->peopleInRoomSummary,
            'people_in_property_count' => $this->peopleInPropertyCount,
        ];
    }
}
