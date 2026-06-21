<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;

class BookingNoShowSavedSearchIntegrationService
{
    public function notifySavedSearchesAfterRelease(BookingNoShow $noShow): void
    {
        app(BookingNoShowEventService::class)->record($noShow, 'saved_searches_notified');
    }

    public function notifyFavoritesAfterRelease(BookingNoShow $noShow): void
    {
        app(BookingNoShowEventService::class)->record($noShow, 'saved_searches_notified', [
            'favorites_notified' => true,
        ]);
    }
}
