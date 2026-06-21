<?php

namespace App\Services\Pricing;

use App\Models\BookingQuote;
use App\Models\SleepingPlacePricingSetting;

class FeeCalculatorService
{
    public function __construct(
        private readonly PricingSettingsService $settings,
    ) {}

    public function calculateEarlyCheckInFee(BookingQuote $quote): float
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);

        return $quote->early_check_in_requested && $settings->early_check_in_mode === SleepingPlacePricingSetting::TIME_MODE_AUTO_FEE
            ? $this->money($settings->early_check_in_fee)
            : 0.0;
    }

    public function calculateLateCheckoutFee(BookingQuote $quote): float
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);

        return $quote->late_check_out_requested && $settings->late_checkout_mode === SleepingPlacePricingSetting::TIME_MODE_AUTO_FEE
            ? $this->money($settings->late_checkout_fee)
            : 0.0;
    }

    public function calculateExtraGuestFee(BookingQuote $quote): float
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);
        $extraGuests = max(0, (int) $quote->guests_count - (int) $settings->included_guests_count);

        if (! $settings->extra_guest_allowed || $extraGuests === 0) {
            return 0.0;
        }

        return $this->money($extraGuests * (int) $quote->nights_count * (float) $settings->extra_guest_fee);
    }

    public function calculateCleaningFee(BookingQuote $quote): float
    {
        return $this->money($this->settings->getForSleepingPlace($quote->sleepingPlace)->cleaning_fee);
    }

    private function money(mixed $amount): float
    {
        return round(max(0, (float) $amount), 2);
    }
}
