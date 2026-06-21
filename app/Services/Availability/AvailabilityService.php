<?php

namespace App\Services\Availability;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarBlock;
use App\Models\User;
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
            && $this->activeDateLockQuery($place, $start, $end)->doesntExist()
            && $this->activeCalendarBlockQuery($place, $start, $end)->doesntExist()
            && $this->blockingAvailabilityQuery($place, $start, $end)->doesntExist()
            && $this->blockingCalendarDayQuery($place, $start, $end)->doesntExist()
            && $this->checkInRestrictionQuery($place, $start)->doesntExist()
            && $this->checkOutRestrictionQuery($place, $end)->doesntExist()
            && $this->calendarCheckInRestrictionQuery($place, $start)->doesntExist()
            && $this->calendarCheckOutRestrictionQuery($place, $end)->doesntExist()
            && $this->bookingModeAllowsRange($place)
            && $this->turnoverAllowsRange($place, $start, $end);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{available:bool,can_instant_book:bool,request_only:bool,status:string,reasons:list<string>}
     */
    public function canBookRange(User $guest, SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut, array $context = []): array
    {
        $start = $this->date($checkIn);
        $end = $this->date($checkOut);
        $reasons = $this->getBlockingReasons($place, $start, $end);
        $status = app(SleepingPlaceCalendarStatusService::class)->resolveRangeStatus($place, $start, $end);
        $settings = $place->calendarSettings()->first();
        $requestOnly = $status === AvailabilityStatus::RequestOnly->value
            || (bool) ($settings?->request_only)
            || $settings?->booking_mode === 'request_only';
        $requiresHostConfirmation = (bool) ($settings?->requires_host_confirmation ?? $settings?->requires_host_approval ?? $place->requires_host_approval);
        $instantMode = ($settings?->booking_mode ?? null) === 'instant' || (bool) ($settings?->instant_booking_enabled ?? $place->instant_booking_enabled);
        $available = $reasons->isEmpty() || ($requestOnly && $reasons->every(fn (string $reason): bool => $reason === 'request_only'));

        return [
            'available' => $available,
            'can_instant_book' => $available && $instantMode && ! $requestOnly && ! $requiresHostConfirmation,
            'request_only' => $requestOnly || ($available && ! $instantMode),
            'status' => $status,
            'reasons' => $reasons->values()->all(),
        ];
    }

    /**
     * @return Collection<int, array{date:string,status:string,public_status:string,check_in_allowed:bool,check_out_allowed:bool}>
     */
    public function getAvailabilityForRange(SleepingPlace $place, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $statuses = app(SleepingPlaceCalendarStatusService::class);

        return collect($this->dateRange($this->date($from), $this->date($to)))
            ->map(function (string $date) use ($place, $statuses): array {
                $calendarDay = $place->calendarDays()->whereDate('date', $date)->first(['check_in_allowed', 'check_out_allowed']);
                $carbon = CarbonImmutable::parse($date);

                return [
                    'date' => $date,
                    'status' => $statuses->resolveDateStatus($place, $carbon),
                    'public_status' => $statuses->getPublicStatus($place, $carbon),
                    'check_in_allowed' => (bool) ($calendarDay?->check_in_allowed ?? true),
                    'check_out_allowed' => (bool) ($calendarDay?->check_out_allowed ?? true),
                ];
            });
    }

    /**
     * @return Collection<int, string>
     */
    public function getBlockingReasons(SleepingPlace $place, CarbonInterface $checkIn, CarbonInterface $checkOut): Collection
    {
        $start = $this->date($checkIn);
        $end = $this->date($checkOut);
        $reasons = collect();

        if ($end->lessThanOrEqualTo($start)) {
            return $reasons->push('invalid_date_range');
        }

        if (! $this->hierarchyIsAvailable($place)) {
            $reasons->push('hierarchy_unavailable');
        }

        if ($this->blockingBookingQuery($place, $start, $end)->exists() || $this->activeDateLockQuery($place, $start, $end)->exists()) {
            $reasons->push('range_overlaps_existing_booking');
        }

        $this->activeCalendarBlockQuery($place, $start, $end)
            ->pluck('block_type')
            ->map(fn (string $type): string => $this->reasonForBlockType($type))
            ->each(fn (string $reason): Collection => $reasons->push($reason));

        $this->blockingAvailabilityQuery($place, $start, $end)
            ->pluck('status')
            ->each(fn (string $status): Collection => $reasons->push($this->reasonForStatus($status)));

        $this->blockingCalendarDayQuery($place, $start, $end)
            ->pluck('status')
            ->each(fn (string $status): Collection => $reasons->push($this->reasonForStatus($status)));

        if ($this->checkInRestrictionQuery($place, $start)->exists() || $this->calendarCheckInRestrictionQuery($place, $start)->exists()) {
            $reasons->push('check_in_not_allowed');
        }

        if ($this->checkOutRestrictionQuery($place, $end)->exists() || $this->calendarCheckOutRestrictionQuery($place, $end)->exists()) {
            $reasons->push('check_out_not_allowed');
        }

        $turnover = app(SleepingPlaceTurnoverService::class)->validateTurnover($place, $start, $end);

        if (! $turnover['allowed']) {
            $reasons->push($turnover['reason_key'] ?? 'turnover_not_allowed');
        }

        if (! $this->bookingModeAllowsRange($place)) {
            $reasons->push('temporarily_hidden');
        }

        $status = app(SleepingPlaceCalendarStatusService::class)->resolveRangeStatus($place, $start, $end);

        if ($status === AvailabilityStatus::RequestOnly->value) {
            $reasons->push('request_only');
        }

        return $reasons->unique()->values();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function canCheckInSameDay(SleepingPlace $place, CarbonInterface $checkIn, array $context = []): bool
    {
        return app(SleepingPlaceTurnoverService::class)
            ->validateTurnover($place, $checkIn, $this->date($checkIn)->addDay(), $context)['allowed'];
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

        $this->activeDateLockQuery($place, $start, $end)
            ->pluck('date')
            ->map(fn ($date): string => $this->date($date)->toDateString())
            ->each(fn (string $date): Collection => $dates->push($date));

        $this->activeCalendarBlockQuery($place, $start, $end)
            ->get(['starts_at', 'ends_at'])
            ->each(function ($block) use ($start, $end, $dates): void {
                $blockStart = $this->date($block->starts_at)->max($start);
                $blockEnd = $this->date($block->ends_at)->min($end);
                $dates->push(...$this->dateRange($blockStart, $blockEnd));
            });

        $this->blockingAvailabilityQuery($place, $start, $end)
            ->pluck('date')
            ->map(fn ($date): string => $this->date($date)->toDateString())
            ->each(fn (string $date): Collection => $dates->push($date));

        $this->blockingCalendarDayQuery($place, $start, $end)
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
            && $this->activeDateLockQuery($place, $start, $end)
                ->where(function (Builder $query) use ($booking): void {
                    $query->whereNull('booking_id')
                        ->orWhere('booking_id', '!=', $booking->id);
                })
                ->doesntExist()
            && $this->activeCalendarBlockQuery($place, $start, $end)
                ->where(function (Builder $query) use ($booking): void {
                    $query->whereNull('booking_id')
                        ->orWhere('booking_id', '!=', $booking->id);
                })
                ->doesntExist()
            && $this->blockingAvailabilityQuery($place, $start, $end)
                ->where($externalAvailability)
                ->doesntExist()
            && $this->blockingCalendarDayQuery($place, $start, $end)
                ->where($externalAvailability)
                ->doesntExist()
            && $this->checkInRestrictionQuery($place, $start)
                ->where($externalAvailability)
                ->doesntExist()
            && $this->checkOutRestrictionQuery($place, $end)
                ->where($externalAvailability)
                ->doesntExist()
            && $this->calendarCheckInRestrictionQuery($place, $start)
                ->where($externalAvailability)
                ->doesntExist()
            && $this->calendarCheckOutRestrictionQuery($place, $end)
                ->where($externalAvailability)
                ->doesntExist()
            && $this->bookingModeAllowsRange($place)
            && $this->turnoverAllowsRange($place, $start, $end);
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

        app(SleepingPlaceDateLockService::class)->releaseLocksForBooking($booking);
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

    private function activeDateLockQuery(SleepingPlace $place, CarbonImmutable $start, CarbonImmutable $end): HasMany
    {
        return $place->bookingDateLocks()
            ->where('status', 'active')
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<', $end->toDateString());
    }

    private function activeCalendarBlockQuery(SleepingPlace $place, CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return SleepingPlaceCalendarBlock::query()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($place): void {
                $query->where('sleeping_place_id', $place->id);

                if ($place->room_id) {
                    $query->orWhere('room_id', $place->room_id);
                }

                if ($place->property_id) {
                    $query->orWhere('property_id', $place->property_id);
                }
            })
            ->where('starts_at', '<', $end->endOfDay())
            ->where('ends_at', '>', $start->startOfDay());
    }

    private function blockingAvailabilityQuery(SleepingPlace $place, CarbonImmutable $start, CarbonImmutable $end): HasMany
    {
        return $place->availabilityDays()
            ->whereIn('status', AvailabilityStatus::blocksStayValues())
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<', $end->toDateString());
    }

    private function blockingCalendarDayQuery(SleepingPlace $place, CarbonImmutable $start, CarbonImmutable $end): HasMany
    {
        return $place->calendarDays()
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

    private function calendarCheckInRestrictionQuery(SleepingPlace $place, CarbonImmutable $start): HasMany
    {
        return $place->calendarDays()
            ->whereDate('date', $start->toDateString())
            ->where(function (Builder $query): void {
                $query->where('check_in_allowed', false)
                    ->orWhere('status', AvailabilityStatus::CheckOutOnly->value);
            });
    }

    private function calendarCheckOutRestrictionQuery(SleepingPlace $place, CarbonImmutable $end): HasMany
    {
        return $place->calendarDays()
            ->whereDate('date', $end->toDateString())
            ->where(function (Builder $query): void {
                $query->where('check_out_allowed', false)
                    ->orWhere('status', AvailabilityStatus::CheckInOnly->value);
            });
    }

    private function bookingModeAllowsRange(SleepingPlace $place): bool
    {
        $place->loadMissing('calendarSettings');

        return $place->calendarSettings?->active !== false
            && $place->calendarSettings?->booking_mode !== 'hidden';
    }

    private function turnoverAllowsRange(SleepingPlace $place, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        return app(SleepingPlaceTurnoverService::class)->validateTurnover($place, $start, $end)['allowed'];
    }

    private function reasonForBlockType(string $blockType): string
    {
        return match ($blockType) {
            'closed_by_host' => 'closed_by_host',
            'closed_by_service_future' => 'closed_by_service_future',
            'cleaning' => 'cleaning_gap_required',
            'repair' => 'repair',
            'breakdown' => 'unavailable_breakdown',
            'complaint' => 'unavailable_complaint',
            'hidden' => 'temporarily_hidden',
            'request_only' => 'request_only',
            default => 'range_overlaps_existing_booking',
        };
    }

    private function reasonForStatus(string $status): string
    {
        return match ($status) {
            'payment_pending', 'pending_payment', 'host_confirmation_pending', 'pending_approval', 'booked', 'guest_checked_in', 'occupied' => 'range_overlaps_existing_booking',
            'closed_by_host', 'blocked_by_host', 'blocked' => 'closed_by_host',
            'closed_by_service_future' => 'closed_by_service_future',
            'cleaning' => 'cleaning_gap_required',
            'repair', 'maintenance' => 'repair',
            'unavailable_breakdown' => 'unavailable_breakdown',
            'unavailable_complaint' => 'unavailable_complaint',
            'temporarily_hidden' => 'temporarily_hidden',
            'request_only' => 'request_only',
            default => 'date_unavailable',
        };
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
