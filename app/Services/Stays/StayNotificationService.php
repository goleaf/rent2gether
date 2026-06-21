<?php

namespace App\Services\Stays;

use App\Models\BookingStay;
use App\Models\Room;

class StayNotificationService
{
    public function notifyStayStarted(BookingStay $stay): void {}

    public function notifyCheckoutSoon(BookingStay $stay): void {}

    public function notifyHostResidentNeedsExtension(BookingStay $stay): void {}

    public function notifyHostResidentHasProblem(BookingStay $stay): void {}

    public function notifyRoommateChange(Room $room): void {}
}
