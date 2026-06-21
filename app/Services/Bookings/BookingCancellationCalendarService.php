<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Services\Availability\SleepingPlaceDateLockService;

class BookingCancellationCalendarService
{
    public function __construct(
        private readonly SleepingPlaceDateLockService $locks,
    ) {}

    public function releaseCalendarLocks(BookingCancellation $cancellation): void
    {
        if ($cancellation->nights_used > 0) {
            $this->keepBlockedAfterCheckInUntilCheckout($cancellation);

            return;
        }

        $this->releaseBeforeCheckInLocks($cancellation);
    }

    public function releaseBeforeCheckInLocks(BookingCancellation $cancellation): void
    {
        $cancellation->loadMissing('booking');

        if ($cancellation->booking) {
            $this->locks->releaseLocksForBooking($cancellation->booking, $cancellation->reason_key);
        }

        $cancellation->forceFill([
            'calendar_release_status' => 'released',
            'dates_released_at' => now(),
        ])->save();
    }

    public function keepBlockedAfterCheckInUntilCheckout(BookingCancellation $cancellation): void
    {
        $cancellation->forceFill([
            'calendar_release_status' => 'kept_blocked',
            'dates_released_at' => null,
        ])->save();
    }

    public function syncAvailabilityAfterCancellation(BookingCancellation $cancellation): void
    {
        $this->releaseCalendarLocks($cancellation);
    }

    public function notifyWaitlistIfDatesReleased(BookingCancellation $cancellation): void
    {
        if ($cancellation->calendar_release_status === 'released') {
            app(BookingCancellationWaitlistIntegrationService::class)->notifyWaitlistForReleasedDates($cancellation);
        }
    }
}
