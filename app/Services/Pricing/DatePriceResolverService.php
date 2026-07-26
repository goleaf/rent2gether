<?php

namespace App\Services\Pricing;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceDatePrice;
use App\Models\SleepingPlacePricingSetting;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

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

        return $this->resolveFromSettings($settings, $date, $override);
    }

    /**
     * @return Collection<string, array{amount:float,line_type:string,source:string,date_price_id:int|null}>
     */
    public function resolveNightPriceDetailsForRange(
        SleepingPlace $place,
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
    ): Collection {
        $settings = $this->settings->getForSleepingPlace($place);
        $overrides = $this->dateOverridesForRange($place, $checkIn, $checkOut);
        $resolved = collect();

        for ($date = CarbonImmutable::instance($checkIn); $date->lessThan(CarbonImmutable::instance($checkOut)); $date = $date->addDay()) {
            $dateKey = $date->toDateString();

            $resolved->put($dateKey, $this->resolveFromSettings(
                $settings,
                $date,
                $overrides->get($dateKey),
            ));
        }

        return $resolved;
    }

    public function getDateOverride(SleepingPlace $place, CarbonInterface $date): ?SleepingPlaceDatePrice
    {
        $dateStart = CarbonImmutable::instance($date)->startOfDay();
        $dateEnd = $dateStart->addDay();
        $dateKey = $dateStart->toDateString();

        if ($place->relationLoaded('datePrices')) {
            return $place->datePrices
                ->filter(fn (SleepingPlaceDatePrice $price): bool => $price->date->toDateString() === $dateKey)
                ->sortBy(fn (SleepingPlaceDatePrice $price): int => $this->datePricePriority($price->price_type))
                ->first();
        }

        return SleepingPlaceDatePrice::query()
            ->select(['id', 'sleeping_place_id', 'date', 'price', 'currency', 'price_type'])
            ->where('sleeping_place_id', $place->id)
            ->where('date', '>=', $dateStart)
            ->where('date', '<', $dateEnd)
            ->get()
            ->sortBy(fn (SleepingPlaceDatePrice $price): int => $this->datePricePriority($price->price_type))
            ->first();
    }

    /**
     * @return array{amount:float,line_type:string,source:string,date_price_id:int|null}
     */
    private function resolveFromSettings(
        SleepingPlacePricingSetting $settings,
        CarbonInterface $date,
        ?SleepingPlaceDatePrice $override,
    ): array {
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

    /**
     * @return Collection<string, SleepingPlaceDatePrice>
     */
    private function dateOverridesForRange(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        $startDate = CarbonImmutable::instance($checkIn)->startOfDay();
        $endDate = CarbonImmutable::instance($checkOut)->startOfDay();
        $start = $startDate->toDateString();
        $end = $endDate->toDateString();

        if ($start >= $end) {
            return collect();
        }

        $prices = $place->relationLoaded('datePrices')
            ? $place->datePrices
                ->filter(fn (SleepingPlaceDatePrice $price): bool => $price->date->toDateString() >= $start
                    && $price->date->toDateString() < $end)
                ->values()
            : SleepingPlaceDatePrice::query()
                ->select(['id', 'sleeping_place_id', 'date', 'price', 'currency', 'price_type'])
                ->where('sleeping_place_id', $place->id)
                ->where('date', '>=', $startDate)
                ->where('date', '<', $endDate)
                ->get();

        return $prices
            ->groupBy(fn (SleepingPlaceDatePrice $price): string => $price->date->toDateString())
            ->map(fn (Collection $datePrices): SleepingPlaceDatePrice => $datePrices
                ->sortBy(fn (SleepingPlaceDatePrice $price): int => $this->datePricePriority($price->price_type))
                ->first());
    }

    private function money(mixed $amount): float
    {
        return round((float) $amount, 2);
    }
}
