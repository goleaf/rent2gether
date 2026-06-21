<?php

namespace App\Services\Pricing;

use App\Models\BookingQuote;
use App\Models\SleepingPlace;

class DepositCalculatorService
{
    public function __construct(
        private readonly PricingSettingsService $settings,
    ) {}

    public function calculateDeposit(BookingQuote $quote): float
    {
        $settings = $this->settings->getForSleepingPlace($quote->sleepingPlace);

        return $settings->deposit_required ? $this->money($settings->deposit_amount) : 0.0;
    }

    public function isDepositRequired(SleepingPlace $place): bool
    {
        return (bool) $this->settings->getForSleepingPlace($place)->deposit_required;
    }

    public function isDepositPayableNow(SleepingPlace $place): bool
    {
        return (bool) $this->settings->getForSleepingPlace($place)->deposit_payable_now;
    }

    /**
     * @return array{message_key:string,refundable:bool}
     */
    public function getDepositReturnExplanation(SleepingPlace $place): array
    {
        $settings = $this->settings->getForSleepingPlace($place);

        return [
            'message_key' => $settings->deposit_refundable
                ? 'pricing.messages.deposit_refundable'
                : 'pricing.messages.deposit_non_refundable',
            'refundable' => (bool) $settings->deposit_refundable,
        ];
    }

    private function money(mixed $amount): float
    {
        return round(max(0, (float) $amount), 2);
    }
}
