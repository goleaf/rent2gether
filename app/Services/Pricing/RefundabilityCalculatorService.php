<?php

namespace App\Services\Pricing;

use App\Models\BookingQuote;

class RefundabilityCalculatorService
{
    public function calculateRefundableAmount(BookingQuote $quote): float
    {
        return $this->money($quote->lines()
            ->where('is_refundable', true)
            ->where('is_payable_now', true)
            ->sum('amount'));
    }

    public function calculateNonRefundableAmount(BookingQuote $quote): float
    {
        return $this->money((float) $quote->total_payable - $this->calculateRefundableAmount($quote));
    }

    /**
     * @return array{refundable:float,non_refundable:float}
     */
    public function splitRefundableAndNonRefundableLines(BookingQuote $quote): array
    {
        return [
            'refundable' => $this->calculateRefundableAmount($quote),
            'non_refundable' => $this->calculateNonRefundableAmount($quote),
        ];
    }

    private function money(mixed $amount): float
    {
        return round(max(0, (float) $amount), 2);
    }
}
