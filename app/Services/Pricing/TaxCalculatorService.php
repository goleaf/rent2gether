<?php

namespace App\Services\Pricing;

use App\Models\BookingQuote;
use App\Models\SleepingPlacePricingSetting;

class TaxCalculatorService
{
    public function __construct(
        private readonly PricingSettingsService $settings,
    ) {}

    public function calculateTaxAmount(BookingQuote $quote): float
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);
        $base = $this->money((float) $quote->accommodation_amount - (float) $quote->discount_amount);

        return $this->calculateByType($base, $settings->tax_fee_type, $settings->tax_fee_value);
    }

    public function calculateCityFee(BookingQuote $quote): float
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);
        $base = $this->money((float) $quote->accommodation_amount - (float) $quote->discount_amount);

        return $this->calculateByType($base, $settings->city_fee_type, $settings->city_fee_value);
    }

    private function calculateByType(float $base, mixed $type, mixed $value): float
    {
        return match ($type) {
            SleepingPlacePricingSetting::FEE_FIXED => $this->money($value),
            SleepingPlacePricingSetting::FEE_PERCENT => $this->money($base * ((float) $value / 100)),
            default => 0.0,
        };
    }

    private function money(mixed $amount): float
    {
        return round(max(0, (float) $amount), 2);
    }
}
