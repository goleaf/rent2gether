<?php

namespace App\Services\HostBulk;

use App\Models\SleepingPlace;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use App\Services\Pricing\PricingSettingsService;
use Illuminate\Support\Collection;

class HostBulkPricingService
{
    public function __construct(
        private readonly HostCalendarDraftService $calendar,
        private readonly PricingSettingsService $pricingSettings,
    ) {}

    public function setPrice(Collection $places, int|float|string $price, ?array $range = null, string $currency = 'EUR'): array
    {
        $affected = 0;

        foreach ($places as $place) {
            if (! $place instanceof SleepingPlace) {
                continue;
            }

            $place->loadMissing('property.host');
            $place->forceFill([
                'base_price_per_night' => $price,
                'base_price' => $price,
                'currency' => $currency,
            ])->save();
            $this->pricingSettings->getForSleepingPlace($place)->forceFill([
                'base_nightly_price' => $price,
                'currency' => strtoupper($currency),
            ])->save();

            if ($range !== null && $place->property?->host) {
                $this->calendar->setPriceForDates($place->property->host, $place, $range, $price);
            }

            $affected++;
        }

        return $this->result($places->count(), $affected);
    }

    public function increasePriceByPercent(Collection $places, float $percent, ?array $range = null): array
    {
        $affected = 0;

        foreach ($places as $place) {
            if (! $place instanceof SleepingPlace) {
                continue;
            }

            $newPrice = round((float) $place->base_price_per_night * (1 + ($percent / 100)), 2);
            $this->setPrice(collect([$place]), $newPrice, $range, $place->currency ?: 'EUR');
            $affected++;
        }

        return $this->result($places->count(), $affected);
    }

    public function decreasePriceByPercent(Collection $places, float $percent, ?array $range = null): array
    {
        return $this->increasePriceByPercent($places, -abs($percent), $range);
    }

    public function setCleaningFee(Collection $places, int|float|string $fee): array
    {
        $affected = 0;

        foreach ($places as $place) {
            if ($place instanceof SleepingPlace) {
                $place->forceFill(['cleaning_fee' => $fee])->save();
                $this->pricingSettings->getForSleepingPlace($place)->forceFill(['cleaning_fee' => $fee])->save();
                $affected++;
            }
        }

        return $this->result($places->count(), $affected);
    }

    public function setWeeklyDiscount(Collection $places, float $percent): array
    {
        return $this->setDiscount($places, 'weekly_discount_percent', $percent);
    }

    public function setMonthlyDiscount(Collection $places, float $percent): array
    {
        return $this->setDiscount($places, 'monthly_discount_percent', $percent);
    }

    private function setDiscount(Collection $places, string $field, float $percent): array
    {
        $affected = 0;

        foreach ($places as $place) {
            if (! $place instanceof SleepingPlace) {
                continue;
            }

            $settings = $this->calendar->getSettings($place);
            $settings->forceFill([$field => $percent])->save();
            $affected++;
        }

        return $this->result($places->count(), $affected);
    }

    private function result(int $selected, int $affected, int $skipped = 0): array
    {
        return [
            'selected_count' => $selected,
            'affected_count' => $affected,
            'skipped_count' => $skipped,
            'failed_count' => 0,
        ];
    }
}
