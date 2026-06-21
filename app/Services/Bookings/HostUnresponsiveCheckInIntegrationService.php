<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;

class HostUnresponsiveCheckInIntegrationService
{
    public function markCheckInHostUnresponsive(BookingHostUnresponsiveCase $case): void
    {
        $checkIn = $case->checkIn()->first();

        if (! $checkIn) {
            return;
        }

        $checkIn->forceFill([
            'has_problem' => true,
            'problem_status' => 'reported',
            'problem_reported_at' => now(),
            'problem_summary' => $case->guest_comment,
            'status' => 'host_unresponsive',
        ])->save();
    }

    public function continueCheckInAfterResolution(BookingHostUnresponsiveCase $case): void
    {
        $checkIn = $case->checkIn()->first();

        if (! $checkIn) {
            return;
        }

        $checkIn->forceFill([
            'problem_status' => 'resolved',
            'status' => 'check_in_continued',
        ])->save();
    }

    public function markCheckInFailedIfUnresolved(BookingHostUnresponsiveCase $case): void
    {
        $checkIn = $case->checkIn()->first();

        if (! $checkIn) {
            return;
        }

        $checkIn->forceFill([
            'problem_status' => 'unresolved',
            'status' => 'failed',
        ])->save();
    }
}
