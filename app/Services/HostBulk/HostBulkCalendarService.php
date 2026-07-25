<?php

namespace App\Services\HostBulk;

use App\Enums\AvailabilityStatus;
use App\Models\SleepingPlace;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
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
        return $this->applyToPlaces($places, function (SleepingPlace $place) use ($range, $reason): bool {
            foreach ($this->period($range) as $date) {
                $dateString = $date->toDateString();

                $place->calendarDays()->updateOrCreate(
                    ['date' => $dateString],
                    [
                        'status' => 'occupied',
                        'currency' => $place->currency,
                        'check_in_allowed' => false,
                        'check_out_allowed' => false,
                        'reason' => $reason,
                        'source' => 'host_bulk',
                        'blocked_by_host' => true,
                    ],
                );

                $place->availabilityDays()->updateOrCreate(
                    ['date' => $dateString],
                    [
                        'status' => AvailabilityStatus::Occupied->value,
                        'check_in_allowed' => false,
                        'check_out_allowed' => false,
                        'note' => $reason,
                    ],
                );
            }

            return true;
        });
    }

    public function setCheckInOutTimes(Collection $places, array $times): array
    {
        return $this->applyToPlaces($places, function (SleepingPlace $place) use ($times): bool {
            $checkInFrom = $times['check_in_time_from'] ?? $times['default_check_in_time'] ?? null;
            $checkInUntil = $times['check_in_time_until'] ?? null;
            $checkOutUntil = $times['check_out_time_until'] ?? $times['default_check_out_time'] ?? null;

            $payload = array_filter([
                'default_check_in_time' => $checkInFrom,
                'earliest_check_in_time' => $checkInFrom,
                'check_in_time_from' => $checkInFrom,
                'check_in_time_until' => $checkInUntil,
                'default_check_out_time' => $checkOutUntil,
                'latest_check_out_time' => $checkOutUntil,
                'check_out_time_until' => $checkOutUntil,
            ], fn (mixed $value): bool => $value !== null && $value !== '');

            if ($payload === []) {
                return false;
            }

            $this->calendar->getSettings($place)->forceFill($payload)->save();

            return true;
        });
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

    /**
     * @return CarbonPeriod<int, CarbonImmutable>
     */
    private function period(array $range): CarbonPeriod
    {
        $start = CarbonImmutable::parse($range['start']);
        $end = CarbonImmutable::parse($range['end']);

        return CarbonPeriod::create($start, $end->subDay());
    }
}
