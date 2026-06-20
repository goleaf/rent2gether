<?php

namespace App\Services\SleepingPlaces;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;

class SleepingPlaceHierarchyService
{
    public function getProperty(SleepingPlace $place): Property
    {
        return $place->relationLoaded('property')
            ? $place->property
            : $place->property()->firstOrFail();
    }

    public function getRoom(SleepingPlace $place): Room
    {
        return $place->relationLoaded('room')
            ? $place->room
            : $place->room()->firstOrFail();
    }

    public function getHost(SleepingPlace $place): User
    {
        $place->loadMissing('host', 'property.host');

        return $place->host ?? $place->property->host;
    }

    /**
     * @return array{sleeping_place: SleepingPlace, room: Room, property: Property, host: User}
     */
    public function getFullContext(SleepingPlace $place): array
    {
        $place->loadMissing('room', 'property.host', 'host');

        return [
            'sleeping_place' => $place,
            'room' => $this->getRoom($place),
            'property' => $this->getProperty($place),
            'host' => $this->getHost($place),
        ];
    }
}
