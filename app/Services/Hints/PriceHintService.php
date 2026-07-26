<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Data\Hints\HintContext;
use App\Data\Occupants\DateRange;
use App\Enums\SleepingPlaceStatus;
use App\Models\Favorite;
use App\Models\Property;
use App\Models\SleepingPlace;
use App\Services\Hints\Concerns\BuildsGuestHints;

class PriceHintService
{
    use BuildsGuestHints;

    /**
     * @var array<int, float|null>
     */
    private array $averageBasePriceByCityId = [];

    public function isCheaperThanAreaAverage(SleepingPlace $place, HintContext $context): ?GuestHintData
    {
        $place->loadMissing('property:id,city_id');
        $cityId = $place->property?->city_id;
        $price = (float) $place->base_price_per_night;

        if (! $cityId || $price <= 0) {
            return null;
        }

        $average = $this->averageBasePriceForCity((int) $cityId);

        if (! $average || $price >= ((float) $average * 0.85)) {
            return null;
        }

        return $this->hint('cheaper_than_area_average', 'price', 'positive', 'medium', 95, card: true, source: 'price');
    }

    public function isMoreExpensiveThanSimilar(SleepingPlace $place, HintContext $context): ?GuestHintData
    {
        $place->loadMissing('property:id,city_id');
        $cityId = $place->property?->city_id;
        $price = (float) $place->base_price_per_night;

        if (! $cityId || $price <= 0) {
            return null;
        }

        $average = $this->averageBasePriceForCity((int) $cityId);

        if (! $average || $price <= ((float) $average * 1.2)) {
            return null;
        }

        return $this->hint('more_expensive_than_similar', 'price', 'warning', 'medium', 45, source: 'price');
    }

    public function hasWeekendPriceChange(SleepingPlace $place, DateRange $range): ?GuestHintData
    {
        if ($place->weekend_price === null || (float) $place->weekend_price === (float) $place->base_price_per_night) {
            return null;
        }

        for ($date = $range->checkIn; $date->lessThan($range->checkOut); $date = $date->addDay()) {
            if ($date->isWeekend()) {
                return $this->hint('weekend_price_change', 'price', 'info', 'medium', 82, card: true, detail: true, source: 'price');
            }
        }

        return null;
    }

    public function hasWeekendPriceDifference(SleepingPlace $place): ?GuestHintData
    {
        if ($place->weekend_price === null || (float) $place->weekend_price === (float) $place->base_price_per_night) {
            return null;
        }

        return $this->hint('weekend_price_change', 'price', 'info', 'low', 50, card: true, source: 'price');
    }

    public function hasWeeklyDiscount(SleepingPlace $place, int $nights): ?GuestHintData
    {
        if ($place->weekly_price === null || ((int) ($place->max_nights ?? 0) > 0 && (int) $place->max_nights < 7)) {
            return null;
        }

        return $this->hint('weekly_discount', 'discount', 'discount', 'medium', $nights >= 7 ? 84 : 48, card: true, source: 'price');
    }

    public function hasMonthlyDiscount(SleepingPlace $place, int $nights): ?GuestHintData
    {
        if ($place->monthly_price === null || ((int) ($place->max_nights ?? 0) > 0 && (int) $place->max_nights < 30)) {
            return null;
        }

        return $this->hint('monthly_discount', 'discount', 'discount', 'medium', $nights >= 30 ? 82 : 47, card: true, source: 'price');
    }

    public function hasDeposit(SleepingPlace $place): ?GuestHintData
    {
        if ((float) ($place->deposit_amount ?? 0) <= 0) {
            return null;
        }

        return $this->hint('deposit_required', 'price', 'warning', 'high', 90, beforeBooking: true, dismissible: false, source: 'price');
    }

    public function hasNoDeposit(SleepingPlace $place): ?GuestHintData
    {
        if ((float) ($place->deposit_amount ?? 0) > 0) {
            return null;
        }

        return $this->hint('no_deposit', 'price', 'positive', 'low', 25, card: true, source: 'price');
    }

    public function hasFreeCancellation(SleepingPlace $place): ?GuestHintData
    {
        $policy = $this->value($place->cancellation_policy);

        if (! in_array($policy, ['free', 'flexible', 'free_cancellation'], true)) {
            return null;
        }

        return $this->hint('free_cancellation', 'cancellation', 'positive', 'medium', 58, card: true, source: 'price');
    }

    public function priceChangedForFavorite(Favorite $favorite): ?GuestHintData
    {
        if (! $favorite->price_changed) {
            return null;
        }

        return $this->hint(
            (float) ($favorite->price_change_amount ?? 0) < 0 ? 'price_dropped' : 'price_increased',
            'price',
            (float) ($favorite->price_change_amount ?? 0) < 0 ? 'positive' : 'warning',
            'medium',
            75,
            favorites: true,
            source: 'favorites',
        );
    }

    private function averageBasePriceForCity(int $cityId): ?float
    {
        if (! array_key_exists($cityId, $this->averageBasePriceByCityId)) {
            $average = SleepingPlace::query()
                ->where('status', SleepingPlaceStatus::Active->value)
                ->whereNotNull('base_price_per_night')
                ->whereIn('property_id', Property::query()->select('id')->where('city_id', $cityId))
                ->avg('base_price_per_night');

            $this->averageBasePriceByCityId[$cityId] = $average === null ? null : (float) $average;
        }

        return $this->averageBasePriceByCityId[$cityId];
    }
}
