<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\SleepingPlace;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function isAvailable(Bed|SleepingPlace $place, CarbonInterface|string $checkIn, CarbonInterface|string $checkOut): bool
    {
        if ($place instanceof Bed) {
            return $this->isLegacyBedAvailable($place, $checkIn, $checkOut);
        }

        $start = $this->date($checkIn);
        $end = $this->date($checkOut);

        if ($end->lessThanOrEqualTo($start)) {
            return false;
        }

        if (! $this->hierarchyIsAvailable($place)) {
            return false;
        }

        return $this->blockingBookingQuery($place, $start, $end)->doesntExist()
            && $this->blockingAvailabilityQuery($place, $start, $end)->doesntExist()
            && $this->checkInRestrictionQuery($place, $start)->doesntExist()
            && $this->checkOutRestrictionQuery($place, $end)->doesntExist();
    }

    /**
     * @return list<string>
     */
    public function unavailableDates(SleepingPlace $place, CarbonInterface|string $checkIn, CarbonInterface|string $checkOut): array
    {
        $start = $this->date($checkIn);
        $end = $this->date($checkOut);

        if ($end->lessThanOrEqualTo($start)) {
            return [];
        }

        if (! $this->hierarchyIsAvailable($place)) {
            return $this->dateRange($start, $end);
        }

        $dates = collect();

        $this->blockingBookingQuery($place, $start, $end)
            ->get(['check_in_date', 'check_out_date'])
            ->each(function (Booking $booking) use ($start, $end, $dates): void {
                $bookingStart = $this->date($booking->check_in_date)->max($start);
                $bookingEnd = $this->date($booking->check_out_date)->min($end);
                $dates->push(...$this->dateRange($bookingStart, $bookingEnd));
            });

        $this->blockingAvailabilityQuery($place, $start, $end)
            ->pluck('date')
            ->map(fn ($date): string => $this->date($date)->toDateString())
            ->each(fn (string $date): Collection => $dates->push($date));

        $this->checkInRestrictionQuery($place, $start)
            ->pluck('date')
            ->map(fn ($date): string => $this->date($date)->toDateString())
            ->each(fn (string $date): Collection => $dates->push($date));

        $this->checkOutRestrictionQuery($place, $end)
            ->pluck('date')
            ->map(fn ($date): string => $this->date($date)->toDateString())
            ->each(fn (string $date): Collection => $dates->push($date));

        return $dates
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return list<array{check_in:string,check_out:string,nights:int}>
     */
    public function nearestAvailableRanges(SleepingPlace $place, CarbonInterface|string $desiredStart, int $nights): array
    {
        $nights = max(1, min(60, $nights));
        $cursor = $this->date($desiredStart);
        $limit = $cursor->addDays(90);
        $ranges = [];

        while ($cursor->lessThanOrEqualTo($limit) && count($ranges) < 3) {
            $checkOut = $cursor->addDays($nights);

            if ($this->isAvailable($place, $cursor, $checkOut)) {
                $ranges[] = [
                    'check_in' => $cursor->toDateString(),
                    'check_out' => $checkOut->toDateString(),
                    'nights' => $nights,
                ];

                $cursor = $checkOut;

                continue;
            }

            $cursor = $cursor->addDay();
        }

        return $ranges;
    }

    public function blockForBooking(Booking $booking): void
    {
        if (! $booking->sleeping_place_id || ! $booking->check_in_date || ! $booking->check_out_date) {
            return;
        }

        $start = $this->date($booking->check_in_date);
        $end = $this->date($booking->check_out_date);

        if ($end->lessThanOrEqualTo($start)) {
            return;
        }

        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace) {
            return;
        }

        foreach ($this->dateRange($start, $end) as $date) {
            $day = $place->availabilityDays()
                ->whereDate('date', $date)
                ->first();

            $attributes = [
                'booking_id' => $booking->id,
                'status' => $this->statusForBooking($booking)->value,
                'check_in_allowed' => false,
                'check_out_allowed' => false,
            ];

            if ($day) {
                $day->update($attributes);

                continue;
            }

            $place->availabilityDays()->create([
                'date' => $date,
                ...$attributes,
            ]);
        }
    }

    public function isAvailableForBooking(Booking $booking): bool
    {
        if (! $booking->sleeping_place_id || ! $booking->check_in_date || ! $booking->check_out_date) {
            return false;
        }

        $place = $booking->sleepingPlace()
            ->with([
                'room:id,property_id,status',
                'property:id,status',
            ])
            ->first();

        if (! $place instanceof SleepingPlace) {
            return false;
        }

        $start = $this->date($booking->check_in_date);
        $end = $this->date($booking->check_out_date);

        if ($end->lessThanOrEqualTo($start)) {
            return false;
        }

        if (! $this->hierarchyIsAvailable($place)) {
            return false;
        }

        $externalAvailability = function (Builder $query) use ($booking): void {
            $query->where(function (Builder $builder) use ($booking): void {
                $builder->whereNull('booking_id')
                    ->orWhere('booking_id', '!=', $booking->id);
            });
        };

        return $this->blockingBookingQuery($place, $start, $end)
            ->whereKeyNot($booking->id)
            ->doesntExist()
            && $this->blockingAvailabilityQuery($place, $start, $end)
                ->where($externalAvailability)
                ->doesntExist()
            && $this->checkInRestrictionQuery($place, $start)
                ->where($externalAvailability)
                ->doesntExist()
            && $this->checkOutRestrictionQuery($place, $end)
                ->where($externalAvailability)
                ->doesntExist();
    }

    public function releaseForBooking(Booking $booking): void
    {
        if (! $booking->sleeping_place_id) {
            return;
        }

        $booking->sleepingPlace?->availabilityDays()
            ->where('booking_id', $booking->id)
            ->whereIn('status', AvailabilityStatus::bookingHoldValues())
            ->update([
                'booking_id' => null,
                'status' => AvailabilityStatus::Available->value,
                'check_in_allowed' => true,
                'check_out_allowed' => true,
            ]);
    }

    private function isLegacyBedAvailable(Bed $bed, CarbonInterface|string $checkIn, CarbonInterface|string $checkOut): bool
    {
        $checkInDate = $this->date($checkIn)->toDateString();
        $checkOutDate = $this->date($checkOut)->toDateString();

        return ! $bed->bookings()
            ->whereNotIn('status', $this->nonBlockingBookingStatuses())
            ->whereDate('check_in', '<', $checkOutDate)
            ->whereDate('check_out', '>', $checkInDate)
            ->exists()
            && ! $bed->availabilities()
                ->whereIn('status', ['blocked', 'maintenance', 'cleaning'])
                ->whereDate('date', '>=', $checkInDate)
                ->whereDate('date', '<', $checkOutDate)
                ->exists();
    }

    private function hierarchyIsAvailable(SleepingPlace $place): bool
    {
        $place->loadMissing([
            'room:id,property_id,status',
            'property:id,status',
        ]);

        return $place->status === SleepingPlaceStatus::Active
            && $place->room?->status === RoomStatus::Active
            && $place->property?->status === PropertyStatus::Active;
    }

    private function blockingBookingQuery(SleepingPlace $place, CarbonImmutable $start, CarbonImmutable $end): HasMany
    {
        return $place->bookings()
            ->whereNotIn('status', $this->nonBlockingBookingStatuses())
            ->whereDate('check_in_date', '<', $end->toDateString())
            ->whereDate('check_out_date', '>', $start->toDateString());
    }

    private function blockingAvailabilityQuery(SleepingPlace $place, CarbonImmutable $start, CarbonImmutable $end): HasMany
    {
        return $place->availabilityDays()
            ->whereIn('status', AvailabilityStatus::blocksStayValues())
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<', $end->toDateString());
    }

    private function checkInRestrictionQuery(SleepingPlace $place, CarbonImmutable $start): HasMany
    {
        return $place->availabilityDays()
            ->whereDate('date', $start->toDateString())
            ->where(function (Builder $query): void {
                $query->where('check_in_allowed', false)
                    ->orWhere('status', AvailabilityStatus::CheckOutOnly->value);
            });
    }

    private function checkOutRestrictionQuery(SleepingPlace $place, CarbonImmutable $end): HasMany
    {
        return $place->availabilityDays()
            ->whereDate('date', $end->toDateString())
            ->where(function (Builder $query): void {
                $query->where('check_out_allowed', false)
                    ->orWhere('status', AvailabilityStatus::CheckInOnly->value);
            });
    }

    private function statusForBooking(Booking $booking): AvailabilityStatus
    {
        return match ($booking->status) {
            BookingStatus::AwaitingPayment,
            BookingStatus::PendingPayment => AvailabilityStatus::PendingPayment,
            BookingStatus::AwaitingHostApproval,
            BookingStatus::Draft,
            BookingStatus::Created,
            BookingStatus::PendingHostConfirmation,
            BookingStatus::PendingGuestResponse => AvailabilityStatus::PendingApproval,
            default => AvailabilityStatus::Booked,
        };
    }

    /**
     * @return list<string>
     */
    private function nonBlockingBookingStatuses(): array
    {
        return [
            BookingStatus::Draft->value,
            BookingStatus::DeclinedByHost->value,
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::Expired->value,
            BookingStatus::CancelledByGuest->value,
            BookingStatus::CancelledByHost->value,
            BookingStatus::CancelledBySystem->value,
            BookingStatus::CancelledByService->value,
            BookingStatus::NoShow->value,
            BookingStatus::HostNoShow->value,
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::AwaitingReview->value,
            BookingStatus::Closed->value,
        ];
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }

    /**
     * @return list<string>
     */
    private function dateRange(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $dates = [];
        $cursor = $start;

        while ($cursor->lessThan($end)) {
            $dates[] = $cursor->toDateString();
            $cursor = $cursor->addDay();
        }

        return $dates;
    }
}
