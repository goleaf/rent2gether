<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Services\Availability\AvailabilityService;
use App\Services\Availability\SleepingPlaceDateLockService;

class BookingCalendarIntegrationService
{
    public function __construct(
        private readonly SleepingPlaceDateLockService $dateLocks,
        private readonly AvailabilityService $availability,
    ) {}

    public function createBookingLocks(Booking $booking): void
    {
        $this->dateLocks->createLocksForBooking($booking);
    }

    public function releaseBookingLocks(Booking $booking, ?string $reason = null): void
    {
        $this->dateLocks->releaseLocksForBooking($booking, $reason);
    }

    public function syncSleepingPlaceCalendar(Booking $booking): void
    {
        $this->availability->blockForBooking($booking);
    }

    public function syncHostCalendar(Booking $booking): void
    {
        $this->syncSleepingPlaceCalendar($booking);
    }
}
