<?php

namespace App\Services\SleepingPlaces;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SleepingPlaceAvailabilityService
{
    public function __construct(
        private readonly AvailabilityService $availability,
    ) {}

    public function isAvailable(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut, array $context = []): bool
    {
        return $this->availability->isAvailable($place, $checkIn, $checkOut);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{available:bool,can_instant_book:bool,request_only:bool,status:string,reasons:list<string>}
     */
    public function canBookRange(User $guest, SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut, array $context = []): array
    {
        return $this->availability->canBookRange($guest, $place, $checkIn, $checkOut, $context);
    }

    public function getAvailabilityForRange(SleepingPlace $place, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->availability->getAvailabilityForRange($place, $from, $to);
    }

    public function getBlockingReasons(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        return $this->availability->getBlockingReasons($place, $checkIn, $checkOut);
    }

    public function canCheckInSameDay(SleepingPlace $place, CarbonInterface $checkIn, array $context = []): bool
    {
        return $this->availability->canCheckInSameDay($place, $checkIn, $context);
    }
}
