<?php

namespace App\Services\Complaints;

use App\Models\BookingListingMismatchReport;
use App\Models\ComplaintCase;
use App\Services\Bookings\ListingMismatchService;

class ComplaintMismatchIntegrationService
{
    public function createMismatchFromComplaint(ComplaintCase $case): ?BookingListingMismatchReport
    {
        if ($case->complaint_type !== 'listing_mismatch') {
            return null;
        }

        $case->loadMissing('booking', 'reporter');

        return app(ListingMismatchService::class)->createFromGuestReport($case->reporter, $case->booking, [
            'source_type' => 'complaint',
            'source_id' => $case->id,
            'mismatch_type' => 'other',
            'severity' => $case->severity,
            'guest_description' => $case->description,
            'guest_wants_relocation' => $case->guest_wants_relocation,
            'guest_wants_cancellation' => $case->guest_wants_cancellation,
            'guest_wants_refund' => $case->guest_wants_refund,
            'guest_wants_compensation' => $case->guest_wants_compensation,
        ]);
    }

    public function linkMismatch(ComplaintCase $case, BookingListingMismatchReport $mismatch): void
    {
        $mismatch->forceFill(['complaint_case_id' => $case->id])->save();
        $case->forceFill(['source_type' => $case->source_type ?: 'listing_mismatch_report', 'source_id' => $case->source_id ?: $mismatch->id])->save();
    }
}
