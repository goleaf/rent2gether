<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;

class ListingMismatchCheckInIntegrationService
{
    public function markCheckInProblem(BookingListingMismatchReport $report): void
    {
        $report->checkIn?->forceFill([
            'has_problem' => true,
            'problem_status' => 'listing_mismatch',
            'status' => 'problem_reported',
            'problem_reported_at' => now(),
            'problem_summary' => $report->guest_description,
        ])->save();
    }

    public function continueCheckInIfResolved(BookingListingMismatchReport $report): void
    {
        $report->checkIn?->forceFill([
            'problem_status' => 'resolved',
            'status' => 'check_in_continued',
        ])->save();
    }

    public function failCheckInIfUnresolved(BookingListingMismatchReport $report): void
    {
        $report->checkIn?->forceFill([
            'problem_status' => 'unresolved',
            'status' => 'failed',
        ])->save();
    }
}
