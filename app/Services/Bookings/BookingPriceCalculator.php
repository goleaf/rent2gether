<?php

namespace App\Services\Bookings;

use App\Models\Bed;
use Carbon\CarbonImmutable;

class BookingPriceCalculator
{
    /**
     * @return array{
     *     nights:int,
     *     calendar_days:int,
     *     subtotal:float,
     *     discount:float,
     *     cleaning_fee:float,
     *     deposit:float,
     *     service_fee:float,
     *     total:float
     * }
     */
    public function calculate(Bed $bed, string $checkIn, string $checkOut): array
    {
        $checkInDate = CarbonImmutable::parse($checkIn);
        $checkOutDate = CarbonImmutable::parse($checkOut);
        $nights = $checkInDate->diffInDays($checkOutDate);
        $calendarDays = $nights + 1;

        $nightlyRate = (float) $bed->price_per_night;
        $subtotal = $nightlyRate * $nights;
        $discount = 0.0;

        if ($nights >= 30 && $bed->price_monthly) {
            $subtotal = (float) $bed->price_monthly;
            $discount = max(0.0, ($nightlyRate * $nights) - $subtotal);
        } elseif ($nights >= 7 && $bed->price_weekly) {
            $subtotal = (float) $bed->price_weekly;
            $discount = max(0.0, ($nightlyRate * $nights) - $subtotal);
        } elseif ($bed->price_weekend) {
            $weekendCount = 0;
            for ($date = $checkInDate; $date->lt($checkOutDate); $date = $date->addDay()) {
                if ($date->isWeekend()) {
                    $weekendCount++;
                }
            }

            if ($weekendCount > 0) {
                $weekdayCount = $nights - $weekendCount;
                $subtotal = ($weekdayCount * $nightlyRate) + ($weekendCount * (float) $bed->price_weekend);
            }
        }

        $cleaningFee = (float) ($bed->cleaning_fee ?? 0);
        $deposit = (float) ($bed->deposit ?? 0);
        $serviceFee = round($subtotal * 0.05, 2);
        $total = $subtotal - $discount + $cleaningFee + $deposit + $serviceFee;

        return [
            'nights' => $nights,
            'calendar_days' => $calendarDays,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'cleaning_fee' => $cleaningFee,
            'deposit' => $deposit,
            'service_fee' => $serviceFee,
            'total' => $total,
        ];
    }
}
