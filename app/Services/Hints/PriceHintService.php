<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Data\Hints\HintContext;
use App\Data\Occupants\DateRange;
use App\Models\Favorite;
use App\Models\SleepingPlace;
use App\Services\Hints\Concerns\BuildsGuestHints;

class PriceHintService
{
    use BuildsGuestHints;

    public function isCheaperThanAreaAverage(SleepingPlace $place, HintContext $context): ?GuestHintData
    {
        $place->loadMissing('property:id,city_id');
        $cityId = $place->property?->city_id;
        $price = (float) $place->base_price_per_night;

        if (! $cityId || $price <= 0) {
            return null;
        }

        $average = SleepingPlace::query()
            ->where('id', '!=', $place->id)
            ->whereNotNull('base_price_per_night')
            ->whereHas('property', fn ($property) => $property->where('city_id', $cityId))
            ->avg('base_price_per_night');

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

        $average = SleepingPlace::query()
            ->where('id', '!=', $place->id)
            ->whereNotNull('base_price_per_night')
            ->whereHas('property', fn ($property) => $property->where('city_id', $cityId))
            ->avg('base_price_per_night');

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
                return $this->hint('weekend_price_change', 'price', 'info', 'low', 50, detail: true, source: 'price');
            }
        }

        return null;
    }

    public function hasWeeklyDiscount(SleepingPlace $place, int $nights): ?GuestHintData
    {
        if ($place->weekly_price === null || $nights < 7) {
            return null;
        }

        return $this->hint('weekly_discount', 'discount', 'discount', 'medium', 84, card: true, source: 'price');
    }

    public function hasMonthlyDiscount(SleepingPlace $place, int $nights): ?GuestHintData
    {
        if ($place->monthly_price === null || $nights < 30) {
            return null;
        }

        return $this->hint('monthly_discount', 'discount', 'discount', 'medium', 82, card: true, source: 'price');
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
}
