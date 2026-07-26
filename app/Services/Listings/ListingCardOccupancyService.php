<?php

namespace App\Services\Listings;

use App\Data\Listings\ListingCardOccupancyData;
use App\Models\Room;
use App\Models\RoomCurrentOccupancySnapshot;
use App\Models\SleepingPlace;

class ListingCardOccupancyService
{
    public function getOccupancy(SleepingPlace $place): ListingCardOccupancyData
    {
        $room = $place->room;
        $snapshot = $this->currentSnapshot($room);
        $roomPlaces = max(1, (int) ($room?->beds_count ?: $room?->max_guests ?: $room?->capacity ?: 1));
        $occupied = max(0, (int) ($snapshot?->occupied_sleeping_places_count ?: $snapshot?->current_occupants_count ?: $room?->occupied_places_count ?: 0));
        $available = max(0, (int) ($snapshot?->free_sleeping_places_count ?? ($room?->available_places_count ?: $roomPlaces - $occupied)));
        $propertyPeople = max(0, (int) ($place->property?->current_guests_count ?: $place->property?->current_residents_count ?: 0));

        return new ListingCardOccupancyData(
            roomPlacesCount: $roomPlaces,
            roomAvailablePlacesCount: $available,
            roomOccupiedPlacesCount: $occupied,
            peopleInRoomSummary: $this->peopleInRoomSummary($occupied, $snapshot),
            peopleInPropertyCount: $propertyPeople,
        );
    }

    private function currentSnapshot(?Room $room): ?RoomCurrentOccupancySnapshot
    {
        if (! $room?->relationLoaded('currentOccupancySnapshot')) {
            return null;
        }

        $snapshot = $room->getRelation('currentOccupancySnapshot');

        return $snapshot instanceof RoomCurrentOccupancySnapshot ? $snapshot : null;
    }

    private function peopleInRoomSummary(int $occupied, ?RoomCurrentOccupancySnapshot $snapshot): string
    {
        if ($occupied <= 0) {
            return __('listing_card.people_in_room_empty');
        }

        $context = $this->peopleContext($snapshot);

        if ($context === []) {
            return trans_choice('listing_card.people_in_room', $occupied, ['count' => $occupied]);
        }

        return trans_choice('listing_card.people_in_room_with_context', $occupied, [
            'count' => $occupied,
            'context' => implode(', ', $context),
        ]);
    }

    /**
     * @return list<string>
     */
    private function peopleContext(?RoomCurrentOccupancySnapshot $snapshot): array
    {
        if (! $snapshot) {
            return [];
        }

        return collect([
            'students' => (int) $snapshot->students_count,
            'workers' => (int) $snapshot->workers_count,
            'tourists' => (int) $snapshot->tourists_count,
            'long_term' => (int) $snapshot->long_term_residents_count,
            'short_term' => (int) $snapshot->short_term_guests_count,
        ])
            ->filter(fn (int $count): bool => $count > 0)
            ->map(fn (int $count, string $key): string => trans_choice('listing_card.people_context.'.$key, $count, ['count' => $count]))
            ->take(3)
            ->values()
            ->all();
    }
}
