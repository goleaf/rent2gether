<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;

class BookingCancellationWaitlistIntegrationService
{
    public function notifyWaitlistForReleasedDates(BookingCancellation $cancellation): void
    {
        app(BookingCancellationEventService::class)->record($cancellation, 'waitlist_notified');
    }

    public function offerCancelledPlaceToNextGuest(BookingCancellation $cancellation): void
    {
        $this->notifyWaitlistForReleasedDates($cancellation);
    }
}
