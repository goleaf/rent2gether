<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;

class BookingNoShowRatingIntegrationService
{
    public function recordConfirmedNoShow(BookingNoShow $noShow): void
    {
        app(BookingNoShowEventService::class)->record($noShow, 'guest_rating_no_show_recorded');
    }

    public function removeNoShowRatingImpactIfRejected(BookingNoShow $noShow): void
    {
        app(BookingNoShowEventService::class)->record($noShow, 'guest_rating_no_show_removed');
    }
}
