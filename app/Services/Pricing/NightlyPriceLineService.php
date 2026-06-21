<?php

namespace App\Services\Pricing;

use App\Models\BookingQuote;
use App\Models\BookingQuoteLine;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class NightlyPriceLineService
{
    public function __construct(
        private readonly DatePriceResolverService $prices,
    ) {}

    /**
     * @return Collection<int, BookingQuoteLine>
     */
    public function buildNightLines(BookingQuote $quote): Collection
    {
        $lines = collect();
        $sort = 1;

        for ($date = CarbonImmutable::instance($quote->check_in_date); $date->lessThan(CarbonImmutable::instance($quote->check_out_date)); $date = $date->addDay()) {
            $lines->push($this->buildLineForDate($quote, $date, $sort++));
        }

        return $lines;
    }

    public function buildLineForDate(BookingQuote $quote, CarbonImmutable $date, int $sortOrder = 1): BookingQuoteLine
    {
        $quote->loadMissing('sleepingPlace');
        $resolved = $this->prices->resolveNightPriceDetails($quote->sleepingPlace, $date);

        return $quote->lines()->create([
            'line_type' => $resolved['line_type'],
            'label_key' => 'pricing.line_types.'.$resolved['line_type'],
            'date' => $date->toDateString(),
            'quantity' => 1,
            'unit_amount' => $resolved['amount'],
            'amount' => $resolved['amount'],
            'currency' => $quote->currency,
            'is_discount' => false,
            'is_fee' => false,
            'is_deposit' => false,
            'is_refundable' => false,
            'is_payable_now' => true,
            'sort_order' => $sortOrder,
        ]);
    }

    public function calculateAccommodationBeforeDiscount(BookingQuote $quote): float
    {
        return $this->money($quote->lines()
            ->whereIn('line_type', ['night', 'weekday_night', 'weekend_night', 'holiday_night', 'date_override'])
            ->sum('amount'));
    }

    private function money(mixed $amount): float
    {
        return round((float) $amount, 2);
    }
}
