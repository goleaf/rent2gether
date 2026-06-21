<?php

namespace App\Services\HostCalendar;

use App\Models\SleepingPlace;
use App\Services\Availability\AvailabilityService;
use App\Services\Availability\SleepingPlaceCalendarStatusService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class HostCalendarViewService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly SleepingPlaceCalendarStatusService $statuses,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function sleepingPlaceCards(SleepingPlace $place, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->availability->getAvailabilityForRange($place, $from, $to)
            ->map(fn (array $day): array => [
                ...$day,
                'title' => $place->title ?? $place->display_name,
                'sleeping_place_id' => $place->id,
                'room_id' => $place->room_id,
                'property_id' => $place->property_id,
                'status_label' => __('availability.statuses.'.$day['status']),
                'priority' => $this->statuses->getStatusPriority($day['status']),
            ]);
    }
}
