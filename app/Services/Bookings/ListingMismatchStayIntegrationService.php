<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;

class ListingMismatchStayIntegrationService
{
    public function markStayProblemReported(BookingListingMismatchReport $report): void
    {
        $report->stay?->forceFill([
            'has_open_complaint' => true,
            'status' => 'problem_reported',
        ])->save();
    }

    public function markStayActiveWithWarning(BookingListingMismatchReport $report): void
    {
        $report->stay?->forceFill([
            'has_open_complaint' => true,
            'status' => 'active_with_warning',
        ])->save();
    }

    public function clearStayWarningIfResolved(BookingListingMismatchReport $report): void
    {
        $report->stay?->forceFill([
            'has_open_complaint' => false,
            'status' => 'active',
        ])->save();
    }
}
