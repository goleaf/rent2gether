<?php

namespace App\Services\Availability;

use App\Models\SleepingPlace;
use Carbon\CarbonInterface;

class GuestCalendarViewService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly SleepingPlaceAvailabilitySuggestionService $suggestions,
    ) {}

    /**
     * @return array{days:array<int,array<string,mixed>>,available_checkouts:array<int,array<string,mixed>>,nearest:array<int,array<string,mixed>>}
     */
    public function forSleepingPlace(SleepingPlace $place, CarbonInterface $from, CarbonInterface $to, ?CarbonInterface $selectedCheckIn = null): array
    {
        $nights = $selectedCheckIn ? max(1, $selectedCheckIn->diffInDays($to)) : 1;

        return [
            'days' => $this->availability->getAvailabilityForRange($place, $from, $to)->all(),
            'available_checkouts' => $selectedCheckIn
                ? $this->suggestions->suggestAvailableCheckOutDates($place, $selectedCheckIn)->all()
                : [],
            'nearest' => $selectedCheckIn
                ? $this->suggestions->suggestNearestAvailableRanges($place, $selectedCheckIn, $nights)->all()
                : [],
        ];
    }
}
