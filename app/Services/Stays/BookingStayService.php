<?php

namespace App\Services\Stays;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingStay;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class BookingStayService
{
    public function __construct(
        private readonly BookingStayNumberService $numbers,
        private readonly BookingStayOccupantService $occupants,
        private readonly StayVisibilityService $visibility,
        private readonly StayEventService $events,
        private readonly CurrentOccupancyService $occupancy,
    ) {}

    public function createFromCheckIn(BookingCheckIn $checkIn): BookingStay
    {
        $checkIn->loadMissing('booking');
        $stay = $this->createForBooking($checkIn->booking()->firstOrFail(), $checkIn);

        $this->occupancy->recalculateAfterCheckIn($checkIn->refresh());

        return $stay->refresh();
    }

    public function createForBooking(Booking $booking, ?BookingCheckIn $checkIn = null): BookingStay
    {
        $booking->loadMissing([
            'guest:id,name,email,phone,city,country,languages,travel_purpose,is_smoker,prefers_quiet,sleep_schedule,rating_as_guest',
            'bookingGuests:id,booking_id,user_id,guest_name,full_name,guest_type,is_main_guest',
        ]);

        $checkIn ??= $booking->checkIn()->first();
        $existing = BookingStay::query()->where('booking_id', $booking->id)->first();
        $stay = $existing ?: new BookingStay([
            'stay_number' => $this->numbers->generate(),
            'booking_id' => $booking->id,
        ]);

        $stay->fill([
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'status' => $existing?->status ?? $this->initialStatus($booking, $checkIn),
            'check_in_date' => $this->dateString($booking->check_in_date ?: $booking->check_in),
            'check_in_time' => $this->timeString($booking->check_in_time),
            'actual_check_in_at' => $checkIn?->checked_in_at ?: $checkIn?->actual_check_in_at ?: $booking->checked_in_at,
            'planned_check_out_date' => $this->dateString($booking->check_out_date ?: $booking->check_out),
            'planned_check_out_time' => $this->timeString($booking->check_out_time),
            'actual_check_out_at' => $booking->checked_out_at,
            'nights_count' => $this->nightsCount($booking),
            'calendar_presence_days_count' => $booking->calendar_presence_days_count ?: $booking->calendar_days_count ?: $this->nightsCount($booking) + 1,
            'payment_status' => $this->value($booking->payment_status),
            'deposit_status' => $booking->deposit_amount > 0 ? 'held_future' : null,
            'cleaning_status' => null,
            'inspection_status' => null,
            'has_open_complaint' => (bool) $booking->has_complaint,
            'has_open_maintenance' => (bool) $booking->has_open_maintenance,
            'has_neighbor_problem' => false,
            'has_payment_problem' => in_array($this->value($booking->payment_status), ['failed', 'unpaid', 'waiting_payment'], true),
            'has_deposit_issue' => (bool) $booking->has_deposit_issue,
            'started_at' => $checkIn?->checked_in_at ?: $checkIn?->actual_check_in_at ?: $booking->checked_in_at ?: now(),
            'ended_at' => $booking->checked_out_at,
            'closed_at' => $booking->closed_at,
        ]);

        $this->refreshCounters($stay);
        $stay->save();

        $this->occupants->createOccupantsFromBooking($booking, $stay->refresh());
        $this->visibility->getVisibilityPreferences($stay->refresh());

        if (! $stay->events()->where('event_key', 'stay_started')->exists()) {
            $this->events->record($stay->refresh(), 'stay_started', [
                'source_type' => $checkIn ? 'booking_check_in' : 'booking',
                'source_id' => $checkIn?->id ?? $booking->id,
            ]);
        }

        return $stay->refresh()->load(['occupants', 'visibilityPreference']);
    }

    public function getForBooking(Booking $booking): ?BookingStay
    {
        return BookingStay::query()
            ->where('booking_id', $booking->id)
            ->first();
    }

    /**
     * @return Collection<int, BookingStay>
     */
    public function getActiveStayForGuest(User $guest): Collection
    {
        return BookingStay::query()
            ->select([
                'id',
                'stay_number',
                'booking_id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'check_in_date',
                'planned_check_out_date',
                'nights_remaining',
            ])
            ->active()
            ->forGuest($guest)
            ->with(['property:id,title,city', 'room:id,title', 'sleepingPlace:id,display_name,place_number'])
            ->orderBy('planned_check_out_date')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getActiveStaysForHost(User $host, array $filters = []): CursorPaginator
    {
        return BookingStay::query()
            ->select([
                'id',
                'stay_number',
                'booking_id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in_date',
                'planned_check_out_date',
                'nights_remaining',
                'checkout_soon',
                'has_open_complaint',
                'has_payment_problem',
                'extension_requested',
                'relocation_requested',
            ])
            ->forHost($host)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['property_id'] ?? null, fn ($query, int $propertyId) => $query->where('property_id', $propertyId))
            ->when($filters['room_id'] ?? null, fn ($query, int $roomId) => $query->where('room_id', $roomId))
            ->with(['guest:id,name,avatar,rating_as_guest,preferred_locale', 'property:id,title', 'room:id,title', 'sleepingPlace:id,display_name,place_number'])
            ->orderBy('planned_check_out_date')
            ->orderBy('id')
            ->cursorPaginate(15);
    }

    public function markActive(BookingStay $stay): BookingStay
    {
        return app(StayStatusService::class)->transition($stay, 'active');
    }

    public function markCheckoutSoon(BookingStay $stay): BookingStay
    {
        return app(StayStatusService::class)->transition($stay, 'checkout_soon');
    }

    public function markCompleted(BookingStay $stay): BookingStay
    {
        return app(StayStatusService::class)->transition($stay, 'completed');
    }

    public function markClosed(BookingStay $stay): BookingStay
    {
        return app(StayStatusService::class)->transition($stay, 'closed');
    }

    private function initialStatus(Booking $booking, ?BookingCheckIn $checkIn): string
    {
        $bookingStatus = $this->value($booking->status);

        if ($checkIn?->checked_in_at || in_array($bookingStatus, [
            BookingStatus::InProgress->value,
            BookingStatus::StayInProgress->value,
            BookingStatus::ActiveStay->value,
        ], true)) {
            return 'active';
        }

        if ($checkIn?->guest_confirmed_at || $checkIn?->host_confirmed_at) {
            return 'pending_check_in_confirmation';
        }

        return 'not_started';
    }

    private function refreshCounters(BookingStay $stay): void
    {
        $checkIn = CarbonImmutable::parse($stay->check_in_date);
        $checkout = CarbonImmutable::parse($stay->planned_check_out_date);
        $today = CarbonImmutable::today();
        $nights = max(0, (int) $checkIn->diffInDays($checkout, false));
        $passed = max(0, min($nights, (int) $checkIn->diffInDays($today, false)));

        $stay->nights_count = $stay->nights_count ?: $nights;
        $stay->calendar_presence_days_count = $stay->calendar_presence_days_count ?: $nights + 1;
        $stay->nights_passed = $passed;
        $stay->nights_remaining = max(0, $nights - $passed);
        $stay->checkout_soon = $stay->checkout_soon || $stay->nights_remaining <= 1;
    }

    private function nightsCount(Booking $booking): int
    {
        if ((int) $booking->nights_count > 0) {
            return (int) $booking->nights_count;
        }

        return max(0, (int) CarbonImmutable::parse($booking->check_in_date ?: $booking->check_in)
            ->diffInDays(CarbonImmutable::parse($booking->check_out_date ?: $booking->check_out), false));
    }

    private function dateString(mixed $date): string
    {
        return is_object($date) && method_exists($date, 'format') ? $date->format('Y-m-d') : (string) $date;
    }

    private function timeString(mixed $time): ?string
    {
        return is_object($time) && method_exists($time, 'format') ? $time->format('H:i') : $time;
    }

    private function value(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return $value === null ? null : (string) $value;
    }
}
