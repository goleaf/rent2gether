<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\User;

class BookingRelocationCheckInOutService
{
    public function __construct(
        private readonly BookingRelocationEventService $events,
    ) {}

    public function createMiniCheckoutForOldPlace(BookingRelocation $relocation): mixed
    {
        $this->events->record($relocation, 'old_place_released_for_inspection');

        return null;
    }

    public function createMiniCheckInForNewPlace(BookingRelocation $relocation): mixed
    {
        $this->events->record($relocation, 'new_booking_segment_created');

        return null;
    }

    public function confirmGuestMoved(User $guest, BookingRelocation $relocation): BookingRelocation
    {
        $this->events->record($relocation, 'guest_consented', ['user_id' => $guest->id, 'moved' => true]);

        return $relocation->refresh();
    }

    public function confirmHostMoved(User $host, BookingRelocation $relocation): BookingRelocation
    {
        $this->events->record($relocation, 'host_consented', ['user_id' => $host->id, 'moved' => true]);

        return $relocation->refresh();
    }
}
