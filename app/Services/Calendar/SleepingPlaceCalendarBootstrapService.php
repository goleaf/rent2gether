<?php

namespace App\Services\Calendar;

use App\Enums\AvailabilityStatus;
use App\Models\AvailabilityDay;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\SleepingPlaceCalendarSetting;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class SleepingPlaceCalendarBootstrapService
{
    public const DEFAULT_OPEN_DAYS = 180;

    /**
     * @return array{settings: SleepingPlaceCalendarSetting, calendar_days: int, availability_days: int}
     */
    public function bootstrap(SleepingPlace $place, CarbonInterface|string|null $startDate = null, ?int $days = null): array
    {
        $settings = $this->ensureSettings($place);
        $start = $this->startDate($startDate);
        $daysToCreate = max(1, $days ?? self::DEFAULT_OPEN_DAYS);
        $calendarRows = [];
        $availabilityRows = [];
        $now = now();

        for ($offset = 0; $offset < $daysToCreate; $offset++) {
            $date = $start->addDays($offset)->toDateString();

            $calendarRows[] = [
                'sleeping_place_id' => $place->id,
                'date' => $date,
                'status' => 'available',
                'price' => null,
                'currency' => $place->currency,
                'min_nights' => $place->min_nights,
                'max_nights' => $place->max_nights,
                'check_in_allowed' => true,
                'check_out_allowed' => true,
                'reason' => null,
                'source' => 'auto_default',
                'booking_id' => null,
                'blocked_by_host' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $availabilityRows[] = [
                'sleeping_place_id' => $place->id,
                'booking_id' => null,
                'date' => $date,
                'status' => AvailabilityStatus::Available->value,
                'price_override' => null,
                'min_nights_override' => $place->min_nights,
                'max_nights_override' => $place->max_nights,
                'check_in_allowed' => true,
                'check_out_allowed' => true,
                'note' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return [
            'settings' => $settings,
            'calendar_days' => SleepingPlaceCalendarDay::query()->insertOrIgnore($calendarRows),
            'availability_days' => AvailabilityDay::query()->insertOrIgnore($availabilityRows),
        ];
    }

    public function ensureSettings(SleepingPlace $place): SleepingPlaceCalendarSetting
    {
        return $place->calendarSettings()->firstOrCreate([], [
            'default_status' => 'available',
            'default_price' => $place->base_price_per_night ?? $place->base_price,
            'currency' => $place->currency,
            'min_nights' => $place->min_nights,
            'max_nights' => $place->max_nights,
            'weekly_discount_percent' => null,
            'monthly_discount_percent' => null,
            'instant_booking_enabled' => (bool) $place->instant_booking_enabled,
            'requires_host_approval' => (bool) $place->requires_host_approval,
            'can_extend' => (bool) ($place->can_extend ?? $place->extensions_allowed ?? true),
            'cleaning_gap_days' => (int) ($place->cleaning_gap_days ?? 0),
        ]);
    }

    private function startDate(CarbonInterface|string|null $startDate): CarbonImmutable
    {
        if ($startDate instanceof CarbonInterface) {
            return CarbonImmutable::instance($startDate)->startOfDay();
        }

        return $startDate
            ? CarbonImmutable::parse($startDate)->startOfDay()
            : CarbonImmutable::today();
    }
}
