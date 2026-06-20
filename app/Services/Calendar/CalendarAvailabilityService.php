<?php

namespace App\Services\Calendar;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

class CalendarAvailabilityService
{
    /**
     * @var list<string>
     */
    private array $blockingStatuses = ['booked', 'blocked', 'cleaning', 'repair', 'pending', 'unavailable'];

    public function isAvailable(SleepingPlace $place, array $range): bool
    {
        return $this->getUnavailableDates($place, $range) === [];
    }

    /**
     * @return list<string>
     */
    public function getUnavailableDates(SleepingPlace $place, array $range): array
    {
        return $place->calendarDays()
            ->whereDate('date', '>=', $this->date($range['start'])->toDateString())
            ->whereDate('date', '<', $this->date($range['end'])->toDateString())
            ->whereIn('status', $this->blockingStatuses)
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($date): string => $this->date($date)->toDateString())
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function getAvailableDates(SleepingPlace $place, array $range): array
    {
        return $place->calendarDays()
            ->whereDate('date', '>=', $this->date($range['start'])->toDateString())
            ->whereDate('date', '<', $this->date($range['end'])->toDateString())
            ->where('status', 'available')
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($date): string => $this->date($date)->toDateString())
            ->values()
            ->all();
    }

    public function canCheckIn(SleepingPlace $place, CarbonInterface|string $date): bool
    {
        $day = $place->calendarDays()->whereDate('date', $this->date($date)->toDateString())->first();

        return ! $day || ($day->status === 'available' && $day->check_in_allowed);
    }

    public function canCheckOut(SleepingPlace $place, CarbonInterface|string $date): bool
    {
        $day = $place->calendarDays()->whereDate('date', $this->date($date)->toDateString())->first();

        return ! $day || ($day->status === 'available' && $day->check_out_allowed);
    }

    public function applyBookingBlock(Booking $booking): void
    {
        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace || ! $booking->check_in_date || ! $booking->check_out_date) {
            return;
        }

        foreach ($this->period(['start' => $booking->check_in_date, 'end' => $booking->check_out_date]) as $date) {
            $dateString = $date->toDateString();
            $place->calendarDays()->updateOrCreate(
                ['date' => $dateString],
                [
                    'status' => $this->statusForBooking($booking),
                    'check_in_allowed' => false,
                    'check_out_allowed' => false,
                    'source' => 'booking',
                    'booking_id' => $booking->id,
                    'blocked_by_host' => false,
                ],
            );
            $place->availabilityDays()->updateOrCreate(
                ['date' => $dateString],
                [
                    'status' => $this->availabilityStatusForBooking($booking),
                    'check_in_allowed' => false,
                    'check_out_allowed' => false,
                    'booking_id' => $booking->id,
                ],
            );
        }
    }

    public function releaseBookingBlock(Booking $booking): void
    {
        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace) {
            return;
        }

        $place->calendarDays()
            ->where('booking_id', $booking->id)
            ->whereIn('status', ['booked', 'pending'])
            ->update([
                'status' => 'available',
                'booking_id' => null,
                'check_in_allowed' => true,
                'check_out_allowed' => true,
            ]);

        $place->availabilityDays()
            ->where('booking_id', $booking->id)
            ->whereIn('status', AvailabilityStatus::bookingHoldValues())
            ->update([
                'status' => AvailabilityStatus::Available->value,
                'booking_id' => null,
                'check_in_allowed' => true,
                'check_out_allowed' => true,
            ]);
    }

    private function statusForBooking(Booking $booking): string
    {
        return in_array($booking->status, [
            BookingStatus::AwaitingHostApproval,
            BookingStatus::AwaitingPayment,
            BookingStatus::PendingPayment,
            BookingStatus::PendingHostConfirmation,
            BookingStatus::PendingGuestResponse,
        ], true) ? 'pending' : 'booked';
    }

    private function availabilityStatusForBooking(Booking $booking): string
    {
        return $this->statusForBooking($booking) === 'pending'
            ? AvailabilityStatus::PendingApproval->value
            : AvailabilityStatus::Booked->value;
    }

    /**
     * @return CarbonPeriod<int, CarbonImmutable>
     */
    private function period(array $range): CarbonPeriod
    {
        return CarbonPeriod::create(
            $this->date($range['start']),
            $this->date($range['end'])->subDay(),
        );
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }
}
