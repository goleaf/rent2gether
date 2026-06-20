<?php

namespace App\Services\HostHints;

use App\Enums\AvailabilityStatus;
use App\Models\SleepingPlace;
use App\Services\HostHints\Concerns\BuildsHostHints;

class HostCalendarHintService
{
    use BuildsHostHints;

    /**
     * @return list<array<string, mixed>>
     */
    public function forSleepingPlace(SleepingPlace $place): array
    {
        $availableCount = $place->availabilityDays()
            ->where('status', AvailabilityStatus::Available->value)
            ->count();
        $availableNext30 = $place->availabilityDays()
            ->where('status', AvailabilityStatus::Available->value)
            ->whereDate('date', '>=', now()->toDateString())
            ->whereDate('date', '<=', now()->addDays(30)->toDateString())
            ->count();

        return collect([
            $availableCount === 0 ? $this->hint('calendar_not_open', 'calendar', 'required', 'critical', 200, 'open_calendar', true, true, true, true) : null,
            $availableCount > 0 && $availableCount < 14 ? $this->hint('few_available_dates', 'calendar', 'suggestion', 'medium', 90, 'open_calendar') : null,
            $availableNext30 === 0 ? $this->hint('no_dates_next_30_days', 'calendar', 'warning', 'medium', 85, 'open_calendar') : null,
            blank($place->min_nights) ? $this->hint('missing_min_nights', 'calendar', 'suggestion', 'medium', 70, 'edit_booking_rules') : null,
            blank($place->max_nights) ? $this->hint('missing_max_nights', 'calendar', 'suggestion', 'low', 55, 'edit_booking_rules') : null,
        ])->filter()->values()->all();
    }
}
