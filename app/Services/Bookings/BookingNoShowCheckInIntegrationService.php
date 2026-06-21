<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;

class BookingNoShowCheckInIntegrationService
{
    public function markCheckInFailedDueToNoShow(BookingNoShow $noShow): void
    {
        $noShow->loadMissing('checkIn');

        if (! $noShow->checkIn) {
            return;
        }

        $noShow->checkIn->forceFill([
            'status' => 'failed',
            'has_problem' => false,
            'closed_at' => now(),
        ])->save();
    }

    public function stopNoShowIfGuestArrived(BookingNoShow $noShow): void
    {
        if ($noShow->guest_claimed_arrived) {
            $noShow->forceFill([
                'status' => 'rejected_no_show',
                'decision_key' => 'rejected_guest_arrived',
            ])->save();
        }
    }

    public function convertToCheckInProblemIfNeeded(BookingNoShow $noShow): mixed
    {
        if ($noShow->guest_response_type === 'i_have_check_in_problem') {
            return app(BookingNoShowDecisionService::class)->convertToCheckInProblem($noShow);
        }

        return null;
    }
}
