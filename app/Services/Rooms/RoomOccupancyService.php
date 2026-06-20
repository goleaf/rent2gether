<?php

namespace App\Services\Rooms;

use App\Models\Room;
use Illuminate\Support\Collection;

class RoomOccupancyService
{
    public function getCurrentOccupancy(Room $room): int
    {
        return (int) ($room->current_guests_count ?: $room->occupied_sleeping_places_count ?: $room->occupied_places_count ?: 0);
    }

    public function getOccupancyForDates(Room $room, mixed $range = null): int
    {
        unset($range);

        return $this->getCurrentOccupancy($room);
    }

    public function countFreePlaces(Room $room, mixed $range = null): int
    {
        unset($range);

        return (int) ($room->free_sleeping_places_count ?: $room->available_places_count ?: 0);
    }

    public function countOccupiedPlaces(Room $room, mixed $range = null): int
    {
        unset($range);

        return (int) ($room->occupied_sleeping_places_count ?: $room->occupied_places_count ?: 0);
    }

    /**
     * @return array{count:int,summary:string}
     */
    public function getPrivacySafeOccupantSummary(Room $room, mixed $range = null): array
    {
        unset($range);

        $count = $this->getCurrentOccupancy($room);

        return [
            'count' => $count,
            'summary' => trans_choice('room.values.privacy_safe_occupants', $count, ['count' => $count]),
        ];
    }

    /**
     * @return Collection<int, never>
     */
    public function privateOccupants(Room $room): Collection
    {
        unset($room);

        return collect();
    }
}
