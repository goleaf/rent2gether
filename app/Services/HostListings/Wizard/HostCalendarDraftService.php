<?php

namespace App\Services\HostListings\Wizard;

use App\Enums\AvailabilityStatus;
use App\Models\Property;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Auth\Access\AuthorizationException;

class HostCalendarDraftService
{
    public function getSettings(SleepingPlace $place): SleepingPlaceCalendarSetting
    {
        return $place->calendarSettings()->firstOrCreate([], [
            'default_status' => 'available',
            'default_price' => $place->base_price_per_night,
            'currency' => $place->currency,
            'min_nights' => $place->min_nights,
            'max_nights' => $place->max_nights,
            'weekly_discount_percent' => null,
            'monthly_discount_percent' => null,
            'instant_booking_enabled' => $place->instant_booking_enabled,
            'requires_host_approval' => $place->requires_host_approval,
            'can_extend' => (bool) $place->can_extend,
            'cleaning_gap_days' => (int) ($place->cleaning_gap_days ?? 0),
        ]);
    }

    public function updateSettings(User $host, SleepingPlace $place, array $data): SleepingPlaceCalendarSetting
    {
        $this->authorizePlace($host, $place);

        $settings = $this->getSettings($place);
        $settings->fill(array_intersect_key($data, array_flip($settings->getFillable())))->save();

        $place->forceFill([
            'base_price_per_night' => $data['default_price'] ?? $place->base_price_per_night,
            'currency' => $data['currency'] ?? $place->currency,
            'min_nights' => $data['min_nights'] ?? $place->min_nights,
            'max_nights' => array_key_exists('max_nights', $data) ? $data['max_nights'] : $place->max_nights,
            'instant_booking_enabled' => $data['instant_booking_enabled'] ?? $place->instant_booking_enabled,
            'requires_host_approval' => $data['requires_host_approval'] ?? $place->requires_host_approval,
            'can_extend' => $data['can_extend'] ?? $place->can_extend,
            'cleaning_gap_days' => $data['cleaning_gap_days'] ?? $place->cleaning_gap_days ?? 0,
        ])->save();

        return $settings->refresh();
    }

    public function openDatesForPlace(User $host, SleepingPlace $place, array $range, array $settings = []): int
    {
        $this->authorizePlace($host, $place);

        $this->writeDates($place, $range, [
            ...$settings,
            'status' => 'available',
            'availability_status' => AvailabilityStatus::Available->value,
            'blocked_by_host' => false,
            'source' => $settings['source'] ?? 'host',
        ]);

        if (array_key_exists('min_nights', $settings) || array_key_exists('max_nights', $settings)) {
            $this->setMinMaxNights($host, $place, $settings['min_nights'] ?? $place->min_nights, $settings['max_nights'] ?? $place->max_nights);
        }

        return $this->rangeCount($range);
    }

    public function closeDatesForPlace(User $host, SleepingPlace $place, array $range, ?string $reason = null): int
    {
        $this->authorizePlace($host, $place);

        $this->writeDates($place, $range, [
            'status' => 'blocked',
            'availability_status' => AvailabilityStatus::BlockedByHost->value,
            'check_in_allowed' => false,
            'check_out_allowed' => false,
            'reason' => $reason,
            'source' => 'host',
            'blocked_by_host' => true,
        ]);

        return $this->rangeCount($range);
    }

    public function bulkOpenDates(User $host, Property $property, array $range, array $settings = []): int
    {
        $this->authorizeProperty($host, $property);

        $count = 0;
        $property->sleepingPlaces()->cursor()->each(function (SleepingPlace $place) use ($host, $range, $settings, &$count): void {
            $count += $this->openDatesForPlace($host, $place, $range, $settings);
        });

        return $count;
    }

    public function bulkCloseDates(User $host, Property $property, array $range, ?string $reason = null): int
    {
        $this->authorizeProperty($host, $property);

        $count = 0;
        $property->sleepingPlaces()->cursor()->each(function (SleepingPlace $place) use ($host, $range, $reason, &$count): void {
            $count += $this->closeDatesForPlace($host, $place, $range, $reason);
        });

        return $count;
    }

    public function setPriceForDates(User $host, SleepingPlace $place, array $range, int|float|string $price): int
    {
        $this->authorizePlace($host, $place);

        $this->writeDates($place, $range, [
            'status' => 'available',
            'availability_status' => AvailabilityStatus::Available->value,
            'price' => $price,
            'source' => 'host_price',
        ]);

        return $this->rangeCount($range);
    }

    public function setCheckInDays(User $host, SleepingPlace $place, array $weekdays): void
    {
        $this->authorizePlace($host, $place);
        $this->syncWeekdayRule($place, 'check_in_days', $weekdays, ['check_in_allowed' => true]);
    }

    public function setCheckOutDays(User $host, SleepingPlace $place, array $weekdays): void
    {
        $this->authorizePlace($host, $place);
        $this->syncWeekdayRule($place, 'check_out_days', $weekdays, ['check_out_allowed' => true]);
    }

    public function setMinMaxNights(User $host, SleepingPlace $place, ?int $min, ?int $max): void
    {
        $this->authorizePlace($host, $place);
        $this->getSettings($place)->forceFill([
            'min_nights' => $min,
            'max_nights' => $max,
        ])->save();
        $place->forceFill([
            'min_nights' => $min,
            'max_nights' => $max,
        ])->save();
    }

    public function setCleaningGap(User $host, SleepingPlace $place, int $hours, int $days): void
    {
        $this->authorizePlace($host, $place);
        $this->getSettings($place)->forceFill([
            'cleaning_gap_hours' => $hours,
            'cleaning_gap_days' => $days,
        ])->save();
        $place->forceFill(['cleaning_gap_days' => $days])->saveQuietly();
    }

    private function writeDates(SleepingPlace $place, array $range, array $settings): void
    {
        foreach ($this->period($range) as $date) {
            $dateString = $date->toDateString();

            $place->calendarDays()->updateOrCreate(
                ['date' => $dateString],
                [
                    'status' => $settings['status'],
                    'price' => $settings['price'] ?? $settings['price_override'] ?? null,
                    'currency' => $settings['currency'] ?? $place->currency,
                    'min_nights' => $settings['min_nights'] ?? null,
                    'max_nights' => $settings['max_nights'] ?? null,
                    'check_in_allowed' => $settings['check_in_allowed'] ?? true,
                    'check_out_allowed' => $settings['check_out_allowed'] ?? true,
                    'reason' => $settings['reason'] ?? null,
                    'source' => $settings['source'] ?? null,
                    'booking_id' => $settings['booking_id'] ?? null,
                    'blocked_by_host' => $settings['blocked_by_host'] ?? false,
                ],
            );

            $place->availabilityDays()->updateOrCreate(
                ['date' => $dateString],
                [
                    'status' => $settings['availability_status'] ?? $this->availabilityStatus($settings['status']),
                    'price_override' => $settings['price'] ?? $settings['price_override'] ?? null,
                    'min_nights_override' => $settings['min_nights'] ?? null,
                    'max_nights_override' => $settings['max_nights'] ?? null,
                    'check_in_allowed' => $settings['check_in_allowed'] ?? true,
                    'check_out_allowed' => $settings['check_out_allowed'] ?? true,
                    'note' => $settings['reason'] ?? null,
                    'booking_id' => $settings['booking_id'] ?? null,
                ],
            );
        }
    }

    private function syncWeekdayRule(SleepingPlace $place, string $type, array $weekdays, array $attributes): void
    {
        $place->calendarRules()->updateOrCreate(
            ['rule_type' => $type],
            [
                'weekdays_json' => array_values($weekdays),
                'priority' => 10,
                ...$attributes,
            ],
        );
    }

    private function availabilityStatus(string $calendarStatus): string
    {
        return match ($calendarStatus) {
            'booked' => AvailabilityStatus::Booked->value,
            'cleaning' => AvailabilityStatus::Cleaning->value,
            'repair' => AvailabilityStatus::Repair->value,
            'blocked', 'unavailable' => AvailabilityStatus::BlockedByHost->value,
            'request_only' => AvailabilityStatus::CheckInOnly->value,
            default => AvailabilityStatus::Available->value,
        };
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

    private function rangeCount(array $range): int
    {
        $start = CarbonImmutable::parse($range['start']);
        $end = CarbonImmutable::parse($range['end']);

        return max(0, $start->diffInDays($end));
    }

    private function authorizePlace(User $host, SleepingPlace $place): void
    {
        $place->loadMissing('property:id,host_user_id,user_id');

        if (! $place->property?->isOwnedBy($host)) {
            throw new AuthorizationException;
        }
    }

    private function authorizeProperty(User $host, Property $property): void
    {
        if (! $property->isOwnedBy($host)) {
            throw new AuthorizationException;
        }
    }
}
