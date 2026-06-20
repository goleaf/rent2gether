<?php

namespace App\Data\Compatibility;

use App\Data\Occupants\DateRange;
use App\Models\GuestCompatibilityProfile;
use App\Models\Room;
use App\Models\RoomCompatibilityProfile;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCompatibilityProfile;
use App\Models\User;

final readonly class CompatibilityContext
{
    /**
     * @param  list<string>  $propertyAmenities
     * @param  list<string>  $propertyRules
     */
    public function __construct(
        public User $guest,
        public GuestCompatibilityProfile $guestProfile,
        public Room $room,
        public SleepingPlace $sleepingPlace,
        public RoomCompatibilityProfile $roomProfile,
        public SleepingPlaceCompatibilityProfile $sleepingPlaceProfile,
        public DateRange $range,
        public array $propertyAmenities = [],
        public array $propertyRules = [],
    ) {}
}
