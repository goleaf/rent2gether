<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;

class BookingCancellationSavedSearchIntegrationService
{
    public function notifySavedSearchesForReleasedPlace(BookingCancellation $cancellation): void
    {
        app(BookingCancellationEventService::class)->record($cancellation, 'saved_searches_notified');
    }

    public function notifyFavoritesIfPlaceAvailableAgain(BookingCancellation $cancellation): void
    {
        $this->notifySavedSearchesForReleasedPlace($cancellation);
    }
}
