<?php

namespace App\Services\HostListings\Wizard;

use App\Models\Room;
use Illuminate\Support\Collection;

class HostListingAutoCreateService
{
    public function __construct(private readonly HostSleepingPlaceDraftService $sleepingPlaces) {}

    public function autoCreatePlacesForRoom(Room $room, int $count): Collection
    {
        return $this->sleepingPlaces->autoCreatePlacesForRoom($room, $count);
    }
}
