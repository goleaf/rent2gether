<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;
use App\Services\Availability\AvailabilityService;
use Carbon\CarbonImmutable;

class SleepingPlaceAvailabilityService
{
    public function __construct(
        private readonly AvailabilityService $availability,
    ) {}

    public function isAvailable(SleepingPlace $place, CarbonImmutable $checkIn, CarbonImmutable $checkOut): bool
    {
        return $this->availability->isAvailable($place, $checkIn, $checkOut);
    }
}
