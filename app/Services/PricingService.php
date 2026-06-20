<?php

namespace App\Services;

use App\Data\BookingPriceQuote;
use App\Models\AvailabilityDay;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class PricingService
{
    private const SERVICE_FEE_RATE = 0.05;

    public function calculate(User $guest, SleepingPlace $sleepingPlace, CarbonInterface|string $checkIn, CarbonInterface|string $checkOut, int $guestsCount = 1): BookingPriceQuote
    {
        $start = $this->date($checkIn);
        $end = $this->date($checkOut);
        $nights = max(0, (int) $start->diffInDays($end));
        $calendarDays = $nights + 1;
        $currency = strtoupper($sleepingPlace->currency ?: 'EUR');
        $baseRate = $this->money($sleepingPlace->base_price_per_night);
        $weekendRate = $sleepingPlace->weekend_price === null ? null : $this->money($sleepingPlace->weekend_price);
        $baseAmount = 0.0;
        $weekendAdjustment = 0.0;
        $dateOverrideAdjustment = 0.0;
        $weekdayCount = 0;
        $weekendCount = 0;
        $datePrices = [];
        $availabilityByDate = $this->availabilityByDate($sleepingPlace, $start, $end);

        for ($date = $start; $date->lessThan($end); $date = $date->addDay()) {
            $dateKey = $date->toDateString();
            $availability = $availabilityByDate[$dateKey] ?? null;
            $baseAmount += $baseRate;
            $nightPrice = $baseRate;
            $source = 'base';

            if ($date->isWeekend()) {
                $weekendCount++;

                if ($weekendRate !== null) {
                    $weekendAdjustment += $weekendRate - $baseRate;
                    $nightPrice = $weekendRate;
                    $source = 'weekend';
                }
            } else {
                $weekdayCount++;
            }

            if ($availability?->price_override !== null) {
                $override = $this->money($availability->price_override);
                $dateOverrideAdjustment += $override - $nightPrice;
                $nightPrice = $override;
                $source = 'date_override';
            }

            $datePrices[] = [
                'date' => $dateKey,
                'weekday' => $date->englishDayOfWeek,
                'price' => $this->money($nightPrice),
                'source' => $source,
            ];
        }

        $stayBeforeDiscount = $this->money($baseAmount + $weekendAdjustment + $dateOverrideAdjustment);
        [$weeklyDiscount, $monthlyDiscount] = $this->discounts($sleepingPlace, $nights, $stayBeforeDiscount);
        $subtotal = $this->money($stayBeforeDiscount - $weeklyDiscount - $monthlyDiscount);
        $cleaningFee = $this->money($sleepingPlace->cleaning_fee ?? 0);
        $deposit = $this->money($sleepingPlace->deposit_amount ?? 0);
        $serviceFee = $this->money($subtotal * self::SERVICE_FEE_RATE);
        $total = $this->money($subtotal + $cleaningFee + $deposit + $serviceFee);
        $refundable = $this->money($deposit);
        $nonRefundable = $this->money($subtotal + $cleaningFee + $serviceFee);
        $lineItems = $this->lineItems(
            currency: $currency,
            baseAmount: $this->money($baseAmount),
            weekendAdjustment: $this->money($weekendAdjustment),
            dateOverrideAdjustment: $this->money($dateOverrideAdjustment),
            weeklyDiscount: $weeklyDiscount,
            monthlyDiscount: $monthlyDiscount,
            cleaningFee: $cleaningFee,
            deposit: $deposit,
            serviceFee: $serviceFee,
            total: $total,
            nights: $nights,
            weekendCount: $weekendCount,
        );

        return new BookingPriceQuote(
            nightsCount: $nights,
            calendarDaysCount: $calendarDays,
            weekdayCount: $weekdayCount,
            weekendCount: $weekendCount,
            checkInWeekday: $start->englishDayOfWeek,
            checkOutWeekday: $end->englishDayOfWeek,
            currency: $currency,
            baseAmount: $this->money($baseAmount),
            dateOverrideAmount: $this->money($dateOverrideAdjustment),
            weekendAdjustmentAmount: $this->money($weekendAdjustment),
            weeklyDiscountAmount: $weeklyDiscount,
            monthlyDiscountAmount: $monthlyDiscount,
            cleaningFeeAmount: $cleaningFee,
            depositAmount: $deposit,
            serviceFeeAmount: $serviceFee,
            subtotalAmount: $subtotal,
            totalAmount: $total,
            refundableAmount: $refundable,
            nonRefundableAmount: $nonRefundable,
            cancellationDeadline: $start->subDay()->setTime(18, 0)->toDateTimeString(),
            paymentDeadline: CarbonImmutable::now()->addMinutes(30)->toDateTimeString(),
            lineItems: $lineItems,
            datePrices: $datePrices,
            warnings: $this->warnings($sleepingPlace, $guestsCount, $nights),
        );
    }

    /**
     * @return array<string, AvailabilityDay>
     */
    private function availabilityByDate(SleepingPlace $sleepingPlace, CarbonImmutable $start, CarbonImmutable $end): array
    {
        if ($sleepingPlace->relationLoaded('availabilityDays')) {
            return $sleepingPlace->availabilityDays
                ->filter(fn (AvailabilityDay $day): bool => $day->price_override !== null
                    && $day->date->greaterThanOrEqualTo($start)
                    && $day->date->lessThan($end))
                ->keyBy(fn (AvailabilityDay $day): string => $day->date->toDateString())
                ->all();
        }

        return $sleepingPlace->availabilityDays()
            ->select(['id', 'sleeping_place_id', 'date', 'price_override'])
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<', $end->toDateString())
            ->whereNotNull('price_override')
            ->get()
            ->keyBy(fn (AvailabilityDay $day): string => $day->date->toDateString())
            ->all();
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function discounts(SleepingPlace $sleepingPlace, int $nights, float $stayBeforeDiscount): array
    {
        if ($nights >= 30 && $sleepingPlace->monthly_price !== null) {
            return [0.0, $this->money(max(0.0, $stayBeforeDiscount - $this->money($sleepingPlace->monthly_price)))];
        }

        if ($nights >= 7 && $sleepingPlace->weekly_price !== null) {
            return [$this->money(max(0.0, $stayBeforeDiscount - $this->money($sleepingPlace->weekly_price))), 0.0];
        }

        return [0.0, 0.0];
    }

    /**
     * @return list<array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool,metadata:array<string, mixed>}>
     */
    private function lineItems(
        string $currency,
        float $baseAmount,
        float $weekendAdjustment,
        float $dateOverrideAdjustment,
        float $weeklyDiscount,
        float $monthlyDiscount,
        float $cleaningFee,
        float $deposit,
        float $serviceFee,
        float $total,
        int $nights,
        int $weekendCount,
    ): array {
        $lines = [
            $this->line('nightly_base', $baseAmount, $currency, false, ['nights' => $nights]),
        ];

        if ($weekendAdjustment !== 0.0) {
            $lines[] = $this->line('weekend_adjustment', $weekendAdjustment, $currency, false, ['weekend_nights' => $weekendCount]);
        }

        if ($dateOverrideAdjustment !== 0.0) {
            $lines[] = $this->line('date_override', $dateOverrideAdjustment, $currency, false);
        }

        if ($weeklyDiscount > 0.0) {
            $lines[] = $this->line('weekly_discount', -$weeklyDiscount, $currency, false);
        }

        if ($monthlyDiscount > 0.0) {
            $lines[] = $this->line('monthly_discount', -$monthlyDiscount, $currency, false);
        }

        if ($cleaningFee > 0.0) {
            $lines[] = $this->line('cleaning_fee', $cleaningFee, $currency, false);
        }

        if ($deposit > 0.0) {
            $lines[] = $this->line('deposit', $deposit, $currency, true);
        }

        $lines[] = $this->line('service_fee', $serviceFee, $currency, false);
        $lines[] = $this->line('total', $total, $currency, false);

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool,metadata:array<string, mixed>}
     */
    private function line(string $type, float $amount, string $currency, bool $refundable, array $metadata = []): array
    {
        return [
            'type' => $type,
            'label_key' => 'booking.price_lines.'.$type,
            'amount' => $this->money($amount),
            'currency' => $currency,
            'is_refundable' => $refundable,
            'metadata' => $metadata,
        ];
    }

    /**
     * @return list<string>
     */
    private function warnings(SleepingPlace $sleepingPlace, int $guestsCount, int $nights): array
    {
        $warnings = [
            'booking.date_selector.warnings.deposit_refundable',
            'booking.date_selector.warnings.review_before_booking',
        ];

        if ($sleepingPlace->min_nights && $nights < $sleepingPlace->min_nights) {
            $warnings[] = 'booking.date_selector.warnings.min_nights';
        }

        if ($sleepingPlace->max_nights && $nights > $sleepingPlace->max_nights) {
            $warnings[] = 'booking.date_selector.warnings.max_nights';
        }

        if ($guestsCount > $sleepingPlace->max_guests) {
            $warnings[] = 'booking.date_selector.warnings.max_guests';
        }

        return $warnings;
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
