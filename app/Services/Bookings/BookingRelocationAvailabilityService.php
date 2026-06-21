<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingRelocation;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\SleepingPlaceCalendarBlock;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingRelocationAvailabilityService
{
    /**
     * @return array{available:bool,reasons:Collection<int, string>}
     */
    public function checkNewPlace(BookingRelocation $relocation): array
    {
        $reasons = $this->getBlockingReasons($relocation);

        return [
            'available' => $reasons->isEmpty(),
            'reasons' => $reasons,
        ];
    }

    /**
     * @return Collection<int, string>
     */
    public function validateRelocationPeriod(BookingRelocation $relocation): Collection
    {
        $start = CarbonImmutable::parse($relocation->new_period_check_in_date)->startOfDay();
        $end = CarbonImmutable::parse($relocation->new_period_check_out_date)->startOfDay();

        return $start->lessThan($end) ? collect() : collect(['new_sleeping_place_unavailable']);
    }

    /**
     * @return Collection<int, string>
     */
    public function getBlockingReasons(BookingRelocation $relocation): Collection
    {
        $reasons = $this->validateRelocationPeriod($relocation);
        $place = $relocation->newSleepingPlace()->with(['property', 'room'])->first();
        $booking = $relocation->originalBooking()->first();

        if (! $place instanceof SleepingPlace || ! $booking instanceof Booking) {
            return $reasons->push('new_sleeping_place_required');
        }

        if ((int) $place->id === (int) $relocation->current_sleeping_place_id) {
            $reasons->push('new_sleeping_place_required');
        }

        if ((int) ($place->property?->host_user_id ?? $place->user_id) !== (int) $relocation->host_user_id) {
            $reasons->push('new_place_not_same_host');
        }

        if (in_array($this->enumValue($place->status), ['repair', 'broken'], true)) {
            $reasons->push('new_place_under_repair');
        }

        if (in_array($this->enumValue($place->publication_status ?: $place->status), ['hidden', 'archived'], true)
            && $relocation->requested_by_type !== 'host') {
            $reasons->push('new_place_hidden');
        }

        $maxGuests = (int) ($place->max_guests_count ?: $place->max_guests ?: 1);
        if ((int) $booking->guests_count > $maxGuests) {
            $reasons->push('guest_count_too_high');
        }

        if ($this->hasActiveLocks($relocation, $place)) {
            $reasons->push('date_locked_by_another_booking');
        }

        if ($this->hasOverlappingBooking($relocation, $place)) {
            $reasons->push('date_locked_by_another_booking');
        }

        if ($this->hasCalendarBlock($relocation, $place, 'repair')) {
            $reasons->push('new_place_under_repair');
        }

        if ($this->hasCalendarBlock($relocation, $place, 'complaint')) {
            $reasons->push('new_place_blocked_by_complaint');
        }

        if ($this->hasRoomOrPropertyBlock($relocation)) {
            $reasons->push('room_policy_mismatch');
        }

        return $reasons->unique()->values();
    }

    public function canRelocateTo(Booking $booking, SleepingPlace $newPlace, Carbon $relocationDate): bool
    {
        $relocation = new BookingRelocation([
            'original_booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'current_property_id' => $booking->property_id,
            'current_room_id' => $booking->room_id,
            'current_sleeping_place_id' => $booking->sleeping_place_id,
            'new_property_id' => $newPlace->property_id,
            'new_room_id' => $newPlace->room_id,
            'new_sleeping_place_id' => $newPlace->id,
            'relocation_date' => $relocationDate->toDateString(),
            'new_period_check_in_date' => $relocationDate->toDateString(),
            'new_period_check_out_date' => $booking->check_out_date,
        ]);
        $relocation->setRelation('newSleepingPlace', $newPlace);
        $relocation->setRelation('originalBooking', $booking);

        return $this->getBlockingReasons($relocation)->isEmpty();
    }

    private function hasActiveLocks(BookingRelocation $relocation, SleepingPlace $place): bool
    {
        return SleepingPlaceBookingDateLock::query()
            ->where('sleeping_place_id', $place->id)
            ->whereIn('date', $this->dateRange($relocation))
            ->where('status', 'active')
            ->where(function ($query) use ($relocation): void {
                $query->whereNull('booking_relocation_id')
                    ->orWhere('booking_relocation_id', '!=', $relocation->id);
            })
            ->exists();
    }

    private function hasOverlappingBooking(BookingRelocation $relocation, SleepingPlace $place): bool
    {
        $start = CarbonImmutable::parse($relocation->new_period_check_in_date)->toDateString();
        $end = CarbonImmutable::parse($relocation->new_period_check_out_date)->toDateString();

        return Booking::query()
            ->where('sleeping_place_id', $place->id)
            ->whereKeyNot($relocation->original_booking_id)
            ->whereIn('status', ['confirmed', 'paid', 'ready_for_check_in', 'guest_checked_in', 'in_progress', 'stay_in_progress'])
            ->whereDate('check_in_date', '<', $end)
            ->whereDate('check_out_date', '>', $start)
            ->exists();
    }

    private function hasCalendarBlock(BookingRelocation $relocation, SleepingPlace $place, string $type): bool
    {
        $start = CarbonImmutable::parse($relocation->new_period_check_in_date)->toDateString();
        $end = CarbonImmutable::parse($relocation->new_period_check_out_date)->toDateString();

        return SleepingPlaceCalendarBlock::query()
            ->where('status', 'active')
            ->where(function ($query) use ($place): void {
                $query->where('sleeping_place_id', $place->id)
                    ->orWhere('room_id', $place->room_id)
                    ->orWhere('property_id', $place->property_id);
            })
            ->where(function ($query) use ($type): void {
                $query->where('block_type', $type)
                    ->orWhere('source_type', $type);
            })
            ->whereDate('check_in_date', '<', $end)
            ->whereDate('check_out_date', '>', $start)
            ->exists();
    }

    private function hasRoomOrPropertyBlock(BookingRelocation $relocation): bool
    {
        $place = $relocation->newSleepingPlace;

        if (! $place instanceof SleepingPlace) {
            return false;
        }

        return in_array($this->enumValue($place->room?->status), ['blocked', 'closed', 'repair'], true)
            || in_array($this->enumValue($place->property?->status), ['blocked', 'closed', 'repair'], true);
    }

    private function enumValue(mixed $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
    }

    /**
     * @return list<string>
     */
    private function dateRange(BookingRelocation $relocation): array
    {
        $dates = [];
        $cursor = CarbonImmutable::parse($relocation->new_period_check_in_date)->startOfDay();
        $end = CarbonImmutable::parse($relocation->new_period_check_out_date)->startOfDay();

        while ($cursor->lessThan($end)) {
            $dates[] = $cursor->toDateString();
            $cursor = $cursor->addDay();
        }

        return $dates;
    }
}
