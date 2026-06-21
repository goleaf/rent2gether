<?php

namespace App\Services\Bookings;

use App\Models\BookingQuote;
use App\Models\BookingQuoteLine;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingQuoteLineService
{
    /**
     * @return Collection<int, BookingQuoteLine>
     */
    public function rebuildLines(BookingQuote $quote): Collection
    {
        $quote->lines()->delete();

        return $this->createNightLines($quote)
            ->merge($this->createDiscountLines($quote))
            ->merge($this->createFeeLines($quote))
            ->merge($this->createTaxLines($quote))
            ->when((float) $quote->deposit_amount > 0, fn (Collection $lines): Collection => $lines->push($this->createDepositLine($quote)))
            ->values();
    }

    /**
     * @return Collection<int, BookingQuoteLine>
     */
    public function createNightLines(BookingQuote $quote): Collection
    {
        $quote->loadMissing('sleepingPlace.calendarDays');
        $place = $quote->sleepingPlace;

        if (! $place instanceof SleepingPlace) {
            return collect();
        }

        $currency = $quote->currency ?: $place->currency ?: 'EUR';
        $baseRate = $this->money($place->base_price_per_night ?: $place->base_price ?: 0);
        $weekendRate = $place->weekend_price === null ? null : $this->money($place->weekend_price);
        $calendarDays = $place->calendarDays
            ->filter(fn ($day): bool => $day->price_override !== null)
            ->keyBy(fn ($day): string => $day->date->toDateString());
        $lines = collect();
        $sort = 1;

        for ($date = CarbonImmutable::instance($quote->check_in_date); $date->lessThan(CarbonImmutable::instance($quote->check_out_date)); $date = $date->addDay()) {
            $calendarDay = $calendarDays->get($date->toDateString());
            $amount = $baseRate;
            $type = $date->isWeekend() && $weekendRate !== null ? 'weekend_night' : 'night';

            if ($date->isWeekend() && $weekendRate !== null) {
                $amount = $weekendRate;
            }

            if ($calendarDay?->price_override !== null) {
                $amount = $this->money($calendarDay->price_override);
                $type = 'date_override';
            }

            $lines->push($quote->lines()->create([
                'line_type' => $type,
                'label_key' => 'booking_quotes.lines.'.$type,
                'date' => $date->toDateString(),
                'quantity' => 1,
                'unit_amount' => $amount,
                'amount' => $amount,
                'currency' => $currency,
                'is_discount' => false,
                'is_fee' => false,
                'is_deposit' => false,
                'is_refundable' => false,
                'is_payable_now' => true,
                'sort_order' => $sort++,
            ]));
        }

        return $lines;
    }

    /**
     * @return Collection<int, BookingQuoteLine>
     */
    public function createDiscountLines(BookingQuote $quote): Collection
    {
        if ((float) $quote->discount_amount <= 0) {
            return collect();
        }

        return collect([$quote->lines()->create([
            'line_type' => $quote->nights_count >= 30 ? 'monthly_discount' : ($quote->nights_count >= 7 ? 'weekly_discount' : 'long_stay_discount'),
            'label_key' => 'booking_quotes.lines.discount',
            'quantity' => 1,
            'unit_amount' => -$this->money($quote->discount_amount),
            'amount' => -$this->money($quote->discount_amount),
            'currency' => $quote->currency,
            'is_discount' => true,
            'is_fee' => false,
            'is_deposit' => false,
            'is_refundable' => false,
            'is_payable_now' => true,
            'sort_order' => 100,
        ])]);
    }

    /**
     * @return Collection<int, BookingQuoteLine>
     */
    public function createFeeLines(BookingQuote $quote): Collection
    {
        $lines = collect();
        $fees = [
            'cleaning_fee' => $quote->cleaning_fee_amount,
            'service_fee' => $quote->service_fee_amount,
        ];

        foreach ($fees as $type => $amount) {
            if ((float) $amount <= 0) {
                continue;
            }

            $lines->push($quote->lines()->create([
                'line_type' => $type,
                'label_key' => 'booking_quotes.lines.'.$type,
                'quantity' => 1,
                'unit_amount' => $this->money($amount),
                'amount' => $this->money($amount),
                'currency' => $quote->currency,
                'is_discount' => false,
                'is_fee' => true,
                'is_deposit' => false,
                'is_refundable' => false,
                'is_payable_now' => true,
                'sort_order' => $type === 'cleaning_fee' ? 200 : 210,
            ]));
        }

        return $lines;
    }

    public function createDepositLine(BookingQuote $quote): BookingQuoteLine
    {
        return $quote->lines()->create([
            'line_type' => 'deposit',
            'label_key' => 'booking_quotes.lines.deposit',
            'quantity' => 1,
            'unit_amount' => $this->money($quote->deposit_amount),
            'amount' => $this->money($quote->deposit_amount),
            'currency' => $quote->currency,
            'is_discount' => false,
            'is_fee' => false,
            'is_deposit' => true,
            'is_refundable' => true,
            'is_payable_now' => true,
            'sort_order' => 300,
        ]);
    }

    /**
     * @return Collection<int, BookingQuoteLine>
     */
    public function createTaxLines(BookingQuote $quote): Collection
    {
        $lines = collect();

        foreach (['tax_future' => $quote->tax_amount, 'city_fee_future' => $quote->city_fee_amount] as $type => $amount) {
            if ((float) $amount <= 0) {
                continue;
            }

            $lines->push($quote->lines()->create([
                'line_type' => $type,
                'label_key' => 'booking_quotes.lines.'.$type,
                'quantity' => 1,
                'unit_amount' => $this->money($amount),
                'amount' => $this->money($amount),
                'currency' => $quote->currency,
                'is_fee' => true,
                'is_refundable' => false,
                'is_payable_now' => true,
                'sort_order' => $type === 'tax_future' ? 220 : 230,
            ]));
        }

        return $lines;
    }

    private function money(mixed $amount): float
    {
        return round((float) $amount, 2);
    }
}
