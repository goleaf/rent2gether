<?php

namespace App\Services\Pricing;

use App\Models\BookingQuote;
use Carbon\Carbon;

class HostPayoutCalculatorService
{
    public function __construct(
        private readonly ServiceFeeCalculatorService $serviceFees,
    ) {}

    public function calculateHostPayout(BookingQuote $quote): float
    {
        $hostBase = $this->money((float) $quote->accommodation_amount - (float) $quote->discount_amount
            + $this->lineAmount($quote, 'early_check_in_fee')
            + $this->lineAmount($quote, 'late_checkout_fee')
            + $this->lineAmount($quote, 'extra_guest_fee')
            + (float) $quote->cleaning_fee_amount);

        return $this->money($hostBase - $this->calculateHostServiceFee($quote));
    }

    public function calculateHostPayoutDate(BookingQuote $quote): Carbon
    {
        return Carbon::instance($quote->check_out_date)->addDay()->setTime(12, 0);
    }

    public function calculateHostServiceFee(BookingQuote $quote): float
    {
        return $this->serviceFees->calculateHostServiceFee($quote);
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
