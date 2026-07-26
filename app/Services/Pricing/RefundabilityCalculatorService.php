<?php

namespace App\Services\Pricing;

use App\Models\BookingQuote;
use App\Models\BookingQuoteLine;
use Illuminate\Support\Collection;

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
     * @param  Collection<int, BookingQuoteLine>  $lines
     */
    public function calculateRefundableAmountFromLines(Collection $lines): float
    {
        return $this->money($lines
            ->filter(fn (BookingQuoteLine $line): bool => (bool) $line->is_refundable && (bool) $line->is_payable_now)
            ->sum(fn (BookingQuoteLine $line): float => (float) $line->amount));
    }

    /**
     * @param  Collection<int, BookingQuoteLine>  $lines
     */
    public function calculateNonRefundableAmountFromLines(float $totalPayable, Collection $lines): float
    {
        return $this->money($totalPayable - $this->calculateRefundableAmountFromLines($lines));
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
