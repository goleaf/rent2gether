<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;

class BookingNoShowWaitlistIntegrationService
{
    public function notifyWaitlistAfterRelease(BookingNoShow $noShow): void
    {
        app(BookingNoShowEventService::class)->record($noShow, 'waitlist_notified');
    }

    public function offerReleasedDatesToNextGuest(BookingNoShow $noShow): void
    {
        app(BookingNoShowEventService::class)->record($noShow, 'waitlist_notified', [
            'offer_next_guest' => true,
        ]);
    }
}
