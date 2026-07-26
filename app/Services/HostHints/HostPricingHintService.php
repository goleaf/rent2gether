<?php

namespace App\Services\HostHints;

use App\Enums\SleepingPlaceStatus;
use App\Models\Property;
use App\Models\SleepingPlace;
use App\Services\HostHints\Concerns\BuildsHostHints;
use Illuminate\Database\Eloquent\Builder;

class HostPricingHintService
{
    use BuildsHostHints;

    /**
     * @return list<array<string, mixed>>
     */
    public function forSleepingPlace(SleepingPlace $place): array
    {
        return collect([
            $this->priceAboveAreaAverage($place),
            $this->priceBelowAreaAverage($place),
            $this->missingCleaningFee($place),
            $this->missingDeposit($place),
            $this->missingWeeklyDiscount($place),
            $this->missingMonthlyDiscount($place),
            $this->missingWeekendPrice($place),
            $this->missingCancellationPolicy($place),
            $this->highPriceWithMissingPhotos($place),
        ])->filter()->values()->all();
    }

    public function priceAboveAreaAverage(SleepingPlace $place): ?array
    {
        $average = $this->areaAverage($place);

        if ($average === null || (float) $place->base_price_per_night <= $average * 1.2) {
            return null;
        }

        return $this->hint('price_above_area_average', 'pricing', 'warning', 'medium', 120, 'review_price');
    }

    public function priceBelowAreaAverage(SleepingPlace $place): ?array
    {
        $average = $this->areaAverage($place);

        if ($average === null || (float) $place->base_price_per_night >= $average * 0.8) {
            return null;
        }

        return $this->hint('price_below_area_average', 'pricing', 'info', 'low', 40, 'review_price');
    }

    public function missingCleaningFee(SleepingPlace $place): ?array
    {
        return (float) $place->cleaning_fee <= 0
            ? $this->hint('missing_cleaning_fee', 'pricing', 'suggestion', 'medium', 95, 'edit_price')
            : null;
    }

    public function missingDeposit(SleepingPlace $place): ?array
    {
        return (float) $place->deposit_amount <= 0
            ? $this->hint('missing_deposit', 'pricing', 'suggestion', 'high', 110, 'edit_deposit', true, true, true, true)
            : null;
    }

    public function missingWeeklyDiscount(SleepingPlace $place): ?array
    {
        return blank($place->weekly_price)
            ? $this->hint('missing_weekly_discount', 'pricing', 'suggestion', 'low', 45, 'edit_discount')
            : null;
    }

    public function missingMonthlyDiscount(SleepingPlace $place): ?array
    {
        return blank($place->monthly_price)
            ? $this->hint('missing_monthly_discount', 'pricing', 'suggestion', 'low', 40, 'edit_discount')
            : null;
    }

    public function missingWeekendPrice(SleepingPlace $place): ?array
    {
        return blank($place->weekend_price)
            ? $this->hint('missing_weekend_price', 'pricing', 'suggestion', 'low', 35, 'edit_price')
            : null;
    }

    public function missingCancellationPolicy(SleepingPlace $place): ?array
    {
        $hasPolicy = filled($place->cancellation_policy) || filled($place->property?->host?->hostProfile?->default_cancellation_policy);

        return $hasPolicy
            ? null
            : $this->hint('missing_cancellation_policy', 'pricing', 'required', 'high', 125, 'edit_cancellation', true, true, true, true);
    }

    public function highPriceWithMissingPhotos(SleepingPlace $place): ?array
    {
        return (float) $place->base_price_per_night >= 70 && $place->mediaItems()->active()->doesntExist()
            ? $this->hint('price_high_without_photos', 'pricing', 'warning', 'medium', 100, 'add_photo')
            : null;
    }

    private function areaAverage(SleepingPlace $place): ?float
    {
        $property = $place->property;

        if (! $property instanceof Property || ! $property->city_id) {
            return null;
        }

        $areaProperties = Property::query()
            ->select('id')
            ->where('city_id', $property->city_id)
            ->when(
                filled($property->district_id),
                fn (Builder $query): Builder => $query->where('district_id', $property->district_id),
                fn (Builder $query): Builder => filled($property->district)
                    ? $query->where('district', $property->district)
                    : $query,
            );

        $average = SleepingPlace::query()
            ->whereKeyNot($place->id)
            ->where('status', SleepingPlaceStatus::Active->value)
            ->where('currency', $place->currency ?: 'EUR')
            ->whereNotNull('base_price_per_night')
            ->where('base_price_per_night', '>', 0)
            ->whereIn('property_id', $areaProperties)
            ->avg('base_price_per_night');

        return $average === null ? null : (float) $average;
    }
}
