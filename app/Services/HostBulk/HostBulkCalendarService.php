<?php

namespace App\Services\HostBulk;

use App\Models\SleepingPlace;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use Illuminate\Support\Collection;

class HostBulkCalendarService
{
    public function __construct(
        private readonly HostBulkPermissionService $permissions,
        private readonly HostCalendarDraftService $calendar,
    ) {}

    public function openDates(Collection $places, array $range, array $settings = []): array
    {
        return $this->applyToPlaces($places, function (SleepingPlace $place) use ($range, $settings): bool {
            if ($this->permissions->hasActiveBookingConflict($place, $range)) {
                return false;
            }

            $place->loadMissing('property.host');
            $this->calendar->openDatesForPlace($place->property->host, $place, $range, $settings);

            return true;
        });
    }

    public function closeDates(Collection $places, array $range, string $reason): array
    {
        return $this->applyToPlaces($places, function (SleepingPlace $place) use ($range, $reason): bool {
            $place->loadMissing('property.host');
            $this->calendar->closeDatesForPlace($place->property->host, $place, $range, $reason);

            return true;
        });
    }

    public function markOccupied(Collection $places, array $range, string $reason): array
    {
        return $this->closeDates($places, $range, $reason);
    }

    public function setCheckInDays(Collection $places, array $weekdays): array
    {
        return $this->applyToPlaces($places, function (SleepingPlace $place) use ($weekdays): bool {
            $place->loadMissing('property.host');
            $this->calendar->setCheckInDays($place->property->host, $place, $weekdays);

            return true;
        });
    }

    public function setCheckOutDays(Collection $places, array $weekdays): array
    {
        return $this->applyToPlaces($places, function (SleepingPlace $place) use ($weekdays): bool {
            $place->loadMissing('property.host');
            $this->calendar->setCheckOutDays($place->property->host, $place, $weekdays);

            return true;
        });
    }

    public function setCleaningGap(Collection $places, int $hours, int $days): array
    {
        return $this->applyToPlaces($places, function (SleepingPlace $place) use ($hours, $days): bool {
            $place->loadMissing('property.host');
            $this->calendar->setCleaningGap($place->property->host, $place, $hours, $days);

            return true;
        });
    }

    private function applyToPlaces(Collection $places, callable $callback): array
    {
        $affected = 0;
        $skipped = 0;

        foreach ($places as $place) {
            if (! $place instanceof SleepingPlace) {
                $skipped++;

                continue;
            }

            $callback($place) ? $affected++ : $skipped++;
        }

        return [
            'selected_count' => $places->count(),
            'affected_count' => $affected,
            'skipped_count' => $skipped,
            'failed_count' => 0,
        ];
    }
}
