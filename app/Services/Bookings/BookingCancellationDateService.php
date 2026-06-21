<?php

namespace App\Services\Bookings;

use App\Models\BookingQuote;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class BookingCancellationDateService
{
    public function calculateFreeCancellationUntil(SleepingPlace $place, CarbonInterface $checkIn): ?CarbonImmutable
    {
        return CarbonImmutable::instance($checkIn)->subDays(5)->setTime(18, 0);
    }

    public function calculatePenaltyStartsAt(SleepingPlace $place, CarbonInterface $checkIn): ?CarbonImmutable
    {
        return $this->calculateFreeCancellationUntil($place, $checkIn);
    }

    /**
     * @return array{free_cancellation_until:?string,cancellation_penalty_starts_at:?string}
     */
    public function buildCancellationPreview(BookingQuote $quote): array
    {
        $quote->loadMissing('sleepingPlace');

        if (! $quote->sleepingPlace instanceof SleepingPlace) {
            return [
                'free_cancellation_until' => null,
                'cancellation_penalty_starts_at' => null,
            ];
        }

        $checkIn = CarbonImmutable::instance($quote->check_in_date);

        return [
            'free_cancellation_until' => $this->calculateFreeCancellationUntil($quote->sleepingPlace, $checkIn)?->toDateTimeString(),
            'cancellation_penalty_starts_at' => $this->calculatePenaltyStartsAt($quote->sleepingPlace, $checkIn)?->toDateTimeString(),
        ];
    }
}
