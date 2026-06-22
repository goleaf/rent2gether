<?php

namespace App\Services\Cleaning;

use App\Models\Booking;
use App\Models\PlaceReadinessCheck;

class CleaningCheckInIntegrationService
{
    public function ensurePlaceReadyBeforeCheckIn(Booking $booking): PlaceReadinessCheck
    {
        $check = app(PlaceReadinessService::class)->createForNextCheckIn($booking);

        return app(PlaceReadinessService::class)->checkReadiness($check);
    }

    public function blockReadyForCheckInIfNotClean(Booking $booking): void
    {
        $this->ensurePlaceReadyBeforeCheckIn($booking);
    }

    public function notifyHostIfPlaceNotReady(Booking $booking): void {}
}
