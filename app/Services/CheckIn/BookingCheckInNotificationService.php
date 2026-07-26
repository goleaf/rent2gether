<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInProblem;
use App\Services\Notifications\CheckInNotificationIntegrationService;

class BookingCheckInNotificationService
{
    public function __construct(
        private readonly CheckInNotificationIntegrationService $notifications,
    ) {}

    public function notifyGuestCheckInSoon(BookingCheckIn $checkIn): void
    {
        $this->notifications->notifyGuestCheckInSoon($checkIn);
    }

    public function notifyGuestCheckInToday(BookingCheckIn $checkIn): void
    {
        $this->notifications->notifyCheckInToday($checkIn);
    }

    public function notifyGuestInstructionsAvailable(BookingCheckIn $checkIn): void
    {
        $this->notifications->notifyInstructionAvailable($checkIn);
    }

    public function notifyHostCheckInSoon(BookingCheckIn $checkIn): void
    {
        $this->notifications->notifyHostCheckInSoon($checkIn);
    }

    public function notifyHostGuestArrived(BookingCheckIn $checkIn): void
    {
        $this->notifications->notifyGuestArrived($checkIn);
    }

    public function notifyHostGuestConfirmed(BookingCheckIn $checkIn): void
    {
        $this->notifications->notifyHostGuestConfirmed($checkIn);
    }

    public function notifyGuestHostConfirmed(BookingCheckIn $checkIn): void
    {
        $this->notifications->notifyGuestHostConfirmed($checkIn);
    }

    public function notifyCheckInProblem(BookingCheckInProblem $problem): void
    {
        $this->notifications->notifyCheckInProblem($problem);
    }
}
