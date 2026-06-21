<?php

namespace App\Services\Stays;

use App\Models\BookingCheckIn;
use App\Models\Property;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\Room;
use App\Models\RoomCurrentOccupancySnapshot;

class CurrentOccupancyService
{
    public function __construct(
        private readonly RoomOccupancySnapshotService $rooms,
        private readonly PropertyOccupancySnapshotService $properties,
    ) {}

    public function recalculateRoom(Room $room): RoomCurrentOccupancySnapshot
    {
        return $this->rooms->refresh($room);
    }

    public function recalculateProperty(Property $property): PropertyCurrentOccupancySnapshot
    {
        return $this->properties->refresh($property);
    }

    public function recalculateAfterCheckIn(BookingCheckIn $checkIn): void
    {
        $checkIn->loadMissing(['room', 'property']);

        if ($checkIn->room) {
            $this->recalculateRoom($checkIn->room);
        }

        if ($checkIn->property) {
            $this->recalculateProperty($checkIn->property);
        }
    }

    public function recalculateAfterCheckOut(mixed $checkOut): void
    {
        if ($checkOut?->room) {
            $this->recalculateRoom($checkOut->room);
        }

        if ($checkOut?->property) {
            $this->recalculateProperty($checkOut->property);
        }
    }

    public function recalculateAfterExtension(mixed $extension): void
    {
        $booking = $extension?->booking;

        if ($booking?->room) {
            $this->recalculateRoom($booking->room);
        }

        if ($booking?->property) {
            $this->recalculateProperty($booking->property);
        }
    }

    public function recalculateAfterRelocation(mixed $relocation): void
    {
        foreach ([$relocation?->oldRoom ?? null, $relocation?->newRoom ?? null] as $room) {
            if ($room instanceof Room) {
                $this->recalculateRoom($room);
            }
        }

        foreach ([$relocation?->oldProperty ?? null, $relocation?->newProperty ?? null] as $property) {
            if ($property instanceof Property) {
                $this->recalculateProperty($property);
            }
        }
    }
}
