<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;
use App\Models\SleepingPlaceBookingDateLock;

class BookingNoShowCalendarService
{
    public function releaseDatesAfterNoShow(BookingNoShow $noShow): void
    {
        if ($noShow->status !== 'confirmed_no_show') {
            $noShow->forceFill(['calendar_release_status' => 'pending'])->save();

            return;
        }

        $this->keepFirstNightBlockedIfPolicyRequires($noShow);
        $this->releaseRemainingDates($noShow->fresh());
        $this->syncAvailabilityAfterNoShow($noShow->fresh());
    }

    public function releaseRemainingDates(BookingNoShow $noShow): void
    {
        $snapshot = app(BookingNoShowPolicySnapshotService::class)->getForBooking($noShow->booking);

        if (! $snapshot->release_remaining_nights_after_no_show) {
            $noShow->forceFill(['calendar_release_status' => 'kept_blocked'])->save();

            return;
        }

        $query = SleepingPlaceBookingDateLock::query()
            ->where('booking_id', $noShow->booking_id)
            ->where('sleeping_place_id', $noShow->sleeping_place_id)
            ->where('status', 'active');

        if ($snapshot->hold_first_night_on_no_show) {
            $query->whereDate('date', '>', $noShow->check_in_date);
        }

        $query->update([
            'status' => 'released',
            'released_at' => now(),
        ]);

        $noShow->forceFill([
            'calendar_release_status' => $snapshot->hold_first_night_on_no_show ? 'released_remaining_dates' : 'released_remaining_dates',
            'dates_released_at' => now(),
        ])->save();
    }

    public function keepFirstNightBlockedIfPolicyRequires(BookingNoShow $noShow): void
    {
        $snapshot = app(BookingNoShowPolicySnapshotService::class)->getForBooking($noShow->booking);

        if (! $snapshot->hold_first_night_on_no_show) {
            return;
        }

        SleepingPlaceBookingDateLock::query()
            ->where('booking_id', $noShow->booking_id)
            ->where('sleeping_place_id', $noShow->sleeping_place_id)
            ->whereDate('date', $noShow->check_in_date)
            ->update([
                'lock_type' => 'no_show_first_night',
                'status' => 'active',
                'released_at' => null,
            ]);
    }

    public function syncAvailabilityAfterNoShow(BookingNoShow $noShow): void
    {
        if ($noShow->calendar_release_status === 'released_remaining_dates') {
            app(BookingNoShowWaitlistIntegrationService::class)->notifyWaitlistAfterRelease($noShow);
            app(BookingNoShowSavedSearchIntegrationService::class)->notifySavedSearchesAfterRelease($noShow);
        }
    }
}
