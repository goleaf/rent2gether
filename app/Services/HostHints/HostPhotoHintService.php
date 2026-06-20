<?php

namespace App\Services\HostHints;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\HostHints\Concerns\BuildsHostHints;

class HostPhotoHintService
{
    use BuildsHostHints;

    /**
     * @return list<array<string, mixed>>
     */
    public function forSleepingPlace(SleepingPlace $place): array
    {
        $property = $place->property;
        $room = $place->room;
        $hints = [];

        if ($this->missingMainSleepingPlacePhoto($place)) {
            $hints[] = $this->hint('add_main_sleeping_place_photo', 'photos', 'required', 'critical', 210, 'add_photo', true, true, true, true);
        }

        if ($place->mediaItems()->active()->count() < 3) {
            $hints[] = $this->hint('add_sleeping_place_photos', 'photos', 'suggestion', 'medium', 130, 'add_photo', true, true, false, true);
        }

        if ($room instanceof Room && $this->missingRoomPhoto($room)) {
            $hints[] = $this->hint('add_room_photos', 'photos', 'suggestion', 'high', 150, 'add_room_photo', true, true, true, true);
        }

        if ($property instanceof Property && ! $this->hasCollectionPhoto($property, 'bathroom')) {
            $hints[] = $this->hint('add_bathroom_photos', 'photos', 'suggestion', 'medium', 80, 'add_bathroom_photo');
        }

        if ($property instanceof Property && ! $this->hasCollectionPhoto($property, 'kitchen')) {
            $hints[] = $this->hint('add_kitchen_photos', 'photos', 'suggestion', 'low', 60, 'add_kitchen_photo');
        }

        if ($place->mediaItems()->active()->doesntExist()) {
            $hints[] = $this->hint('listing_without_photos_gets_less_requests', 'photos', 'info', 'medium', 90, 'add_photo');
        }

        return $hints;
    }

    public function missingMainSleepingPlacePhoto(SleepingPlace $place): bool
    {
        return ! $place->mediaItems()->active()->primary()->exists();
    }

    public function missingRoomPhoto(Room $room): bool
    {
        return $room->mediaItems()->active()->doesntExist();
    }

    public function hasCollectionPhoto(Property $property, string $collection): bool
    {
        return $property->mediaItems()
            ->active()
            ->where('collection', $collection)
            ->exists();
    }
}
