<?php

namespace App\Services\Hints;

use App\Data\Hints\GuestHintData;
use App\Data\Occupants\DateRange;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\AvailabilityService;
use App\Services\Hints\Concerns\BuildsGuestHints;

class AvailabilityHintService
{
    use BuildsGuestHints;

    public function __construct(private readonly AvailabilityService $availability) {}

    public function hasOnePlaceLeft(Room $room, DateRange $range): ?GuestHintData
    {
        $available = (int) ($room->available_places_count ?? $room->free_sleeping_places_count ?? 0);

        if ($available !== 1) {
            return null;
        }

        return $this->hint('one_place_left', 'availability', 'urgent', 'high', 96, card: true, beforeBooking: true, dismissible: false, source: 'availability');
    }

    public function isAvailableForLongerStay(SleepingPlace $place, DateRange $range): ?GuestHintData
    {
        if (! $place->can_extend && ! $place->extensions_allowed) {
            return null;
        }

        return $this->hint('available_for_longer_stay', 'availability', 'info', 'low', 35, source: 'availability');
    }

    public function isPartiallyAvailable(SleepingPlace $place, DateRange $range): ?GuestHintData
    {
        return null;
    }

    public function canExtend(SleepingPlace $place, DateRange $range): ?GuestHintData
    {
        if (! $place->can_extend && ! $place->extensions_allowed) {
            return null;
        }

        return $this->hint('can_extend', 'availability', 'positive', 'low', 42, card: true, source: 'availability');
    }

    public function canCheckInToday(SleepingPlace $place): ?GuestHintData
    {
        if (! $this->availability->isAvailable($place, now()->toDateString(), now()->addDay()->toDateString())) {
            return null;
        }

        return $this->hint('can_check_in_today', 'availability', 'info', 'medium', 62, card: true, source: 'availability');
    }

    public function isInstantBooking(SleepingPlace $place): ?GuestHintData
    {
        if (! $place->instant_booking_enabled || $place->requires_host_approval) {
            return null;
        }

        return $this->hint('instant_booking', 'availability', 'positive', 'medium', 78, card: true, source: 'availability');
    }

    public function requiresHostApproval(SleepingPlace $place): ?GuestHintData
    {
        if (! $place->requires_host_approval) {
            return null;
        }

        return $this->hint('host_approval_required', 'availability', 'info', 'medium', 55, beforeBooking: true, source: 'availability');
    }
}
