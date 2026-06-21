<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInProblem;

class BookingCheckInNotificationService
{
    public function notifyGuestCheckInSoon(BookingCheckIn $checkIn): void {}

    public function notifyGuestCheckInToday(BookingCheckIn $checkIn): void {}

    public function notifyGuestInstructionsAvailable(BookingCheckIn $checkIn): void {}

    public function notifyHostCheckInSoon(BookingCheckIn $checkIn): void {}

    public function notifyHostGuestArrived(BookingCheckIn $checkIn): void {}

    public function notifyHostGuestConfirmed(BookingCheckIn $checkIn): void {}

    public function notifyGuestHostConfirmed(BookingCheckIn $checkIn): void {}

    public function notifyCheckInProblem(BookingCheckInProblem $problem): void {}
}
