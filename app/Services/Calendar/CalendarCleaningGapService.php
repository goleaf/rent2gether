<?php

namespace App\Services\Calendar;

use App\Enums\AvailabilityStatus;
use App\Models\Booking;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;

class CalendarCleaningGapService
{
    public function blockAfterCheckout(Booking $booking): void
    {
        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace || ! $booking->check_out_date) {
            return;
        }

        foreach ($this->calculateCleaningDates($booking) as $date) {
            $place->calendarDays()->updateOrCreate(
                ['date' => $date],
                [
                    'status' => 'cleaning',
                    'check_in_allowed' => false,
                    'check_out_allowed' => false,
                    'reason' => 'cleaning_gap',
                    'source' => 'cleaning_gap',
                    'booking_id' => $booking->id,
                    'blocked_by_host' => false,
                ],
            );
            $place->availabilityDays()->updateOrCreate(
                ['date' => $date],
                [
                    'status' => AvailabilityStatus::Cleaning->value,
                    'check_in_allowed' => false,
                    'check_out_allowed' => false,
                    'note' => 'cleaning_gap',
                    'booking_id' => $booking->id,
                ],
            );
        }
    }

    public function releaseCleaningBlocks(Booking $booking): void
    {
        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace) {
            return;
        }

        $place->calendarDays()
            ->where('booking_id', $booking->id)
            ->where('status', 'cleaning')
            ->update([
                'status' => 'available',
                'booking_id' => null,
                'check_in_allowed' => true,
                'check_out_allowed' => true,
                'reason' => null,
            ]);

        $place->availabilityDays()
            ->where('booking_id', $booking->id)
            ->where('status', AvailabilityStatus::Cleaning->value)
            ->update([
                'status' => AvailabilityStatus::Available->value,
                'booking_id' => null,
                'check_in_allowed' => true,
                'check_out_allowed' => true,
                'note' => null,
            ]);
    }

    /**
     * @return list<string>
     */
    public function calculateCleaningDates(Booking $booking): array
    {
        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace || ! $booking->check_out_date) {
            return [];
        }

        $settings = $place->calendarSettings;
        $days = max(0, (int) ($settings?->cleaning_gap_days ?? $place->cleaning_gap_days ?? 0));

        if ($days < 1) {
            return [];
        }

        $start = CarbonImmutable::parse($booking->check_out_date)->startOfDay();

        return collect(range(0, $days - 1))
            ->map(fn (int $offset): string => $start->addDays($offset)->toDateString())
            ->all();
    }
}
