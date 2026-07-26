<?php

namespace App\Services\Pricing;

use App\Models\BookingQuote;
use App\Models\SleepingPlacePricingSetting;

class ServiceFeeCalculatorService
{
    public function __construct(
        private readonly PricingSettingsService $settings,
    ) {}

    public function calculateGuestServiceFee(BookingQuote $quote): float
    {
        return $this->calculateGuestServiceFeeForBase($quote, $this->quoteBaseForFees($quote));
    }

    public function calculateHostServiceFee(BookingQuote $quote): float
    {
        return $this->calculateHostServiceFeeForBase($quote, $this->quoteBaseForFees($quote));
    }

    public function calculateGuestServiceFeeForBase(BookingQuote $quote, float $base): float
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);

        return $this->calculateByType($this->money($base), $settings->guest_service_fee_type, $settings->guest_service_fee_value);
    }

    public function calculateHostServiceFeeForBase(BookingQuote $quote, float $base): float
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);

        return $this->calculateByType($this->money($base), $settings->host_service_fee_type, $settings->host_service_fee_value);
    }

    private function quoteBaseForFees(BookingQuote $quote): float
    {
        return $this->money((float) $quote->accommodation_amount - (float) $quote->discount_amount
            + $this->lineAmount($quote, 'early_check_in_fee')
            + $this->lineAmount($quote, 'late_checkout_fee')
            + $this->lineAmount($quote, 'extra_guest_fee'));
    }

    private function calculateByType(float $base, mixed $type, mixed $value): float
    {
        return match ($type) {
            SleepingPlacePricingSetting::FEE_FIXED => $this->money($value),
            SleepingPlacePricingSetting::FEE_PERCENT => $this->money($base * ((float) $value / 100)),
            default => 0.0,
        };
    }

    private function lineAmount(BookingQuote $quote, string $type): float
    {
        return $this->money($quote->lines()->where('line_type', $type)->sum('amount'));
    }

    private function money(mixed $amount): float
    {
        return round(max(0, (float) $amount), 2);
    }
}
