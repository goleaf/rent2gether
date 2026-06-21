<?php

namespace App\Services\Pricing;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceDatePrice;
use Carbon\CarbonInterface;

class DatePriceResolverService
{
    public function __construct(
        private readonly PricingSettingsService $settings,
    ) {}

    public function resolveNightPrice(SleepingPlace $place, CarbonInterface $date): float
    {
        return $this->resolveNightPriceDetails($place, $date)['amount'];
    }

    /**
     * @return array{amount:float,line_type:string,source:string,date_price_id:int|null}
     */
    public function resolveNightPriceDetails(SleepingPlace $place, CarbonInterface $date): array
    {
        $settings = $this->settings->getForSleepingPlace($place);
        $override = $this->getDateOverride($place, $date);

        if ($override instanceof SleepingPlaceDatePrice) {
            return [
                'amount' => $this->money($override->price),
                'line_type' => 'date_override',
                'source' => $override->price_type,
                'date_price_id' => $override->id,
            ];
        }

        if ($settings->holiday_price !== null && $this->isHoliday($date, '')) {
            return [
                'amount' => $this->money($settings->holiday_price),
                'line_type' => 'holiday_night',
                'source' => 'holiday',
                'date_price_id' => null,
            ];
        }

        if ($settings->weekend_price !== null && $this->isWeekend($date, $settings->weekend_days_json ?: [])) {
            return [
                'amount' => $this->money($settings->weekend_price),
                'line_type' => 'weekend_night',
                'source' => 'weekend',
                'date_price_id' => null,
            ];
        }

        if ($settings->weekday_price !== null && ! $this->isWeekend($date, $settings->weekend_days_json ?: [])) {
            return [
                'amount' => $this->money($settings->weekday_price),
                'line_type' => 'weekday_night',
                'source' => 'weekday',
                'date_price_id' => null,
            ];
        }

        return [
            'amount' => $this->money($settings->base_nightly_price),
            'line_type' => 'night',
            'source' => 'base',
            'date_price_id' => null,
        ];
    }

    public function getDateOverride(SleepingPlace $place, CarbonInterface $date): ?SleepingPlaceDatePrice
    {
        $prices = $place->datePrices()
            ->whereDate('date', $date->toDateString())
            ->get();

        return $prices
            ->sortBy(fn (SleepingPlaceDatePrice $price): int => $this->datePricePriority($price->price_type))
            ->first();
    }

    /**
     * @param  array<int, string>  $weekendDays
     */
    public function isWeekend(CarbonInterface $date, array $weekendDays): bool
    {
        $days = $weekendDays !== [] ? $weekendDays : ['saturday', 'sunday'];

        return in_array(strtolower($date->englishDayOfWeek), array_map('strtolower', $days), true);
    }

    public function isHoliday(CarbonInterface $date, string $countryCode): bool
    {
        return $date->format('m-d') === '01-01';
    }

    private function datePricePriority(string $type): int
    {
        return match ($type) {
            SleepingPlaceDatePrice::TYPE_MANUAL_OVERRIDE => 1,
            SleepingPlaceDatePrice::TYPE_HOLIDAY => 2,
            SleepingPlaceDatePrice::TYPE_WEEKEND_OVERRIDE => 3,
            SleepingPlaceDatePrice::TYPE_SEASONAL => 4,
            SleepingPlaceDatePrice::TYPE_SPECIAL_EVENT => 5,
            default => 10,
        };
    }

    private function money(mixed $amount): float
    {
        return round((float) $amount, 2);
    }
}
