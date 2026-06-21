<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;

class BookingCancellationComplaintIntegrationService
{
    public function createComplaintIfHousingProblem(BookingCancellation $cancellation): mixed
    {
        if ($cancellation->reason_key === 'housing_problem') {
            app(BookingCancellationEventService::class)->record($cancellation, 'complaint_created');
        }

        return null;
    }

    public function linkMismatchReportIfNeeded(BookingCancellation $cancellation): void
    {
        if ($cancellation->reason_key === 'listing_mismatch') {
            app(BookingCancellationEventService::class)->record($cancellation, 'complaint_created', ['source_type' => 'listing_mismatch']);
        }
    }

    public function openDisputeIfRefundDisagreed(BookingCancellation $cancellation): mixed
    {
        app(BookingCancellationEventService::class)->record($cancellation, 'dispute_opened');

        return null;
    }
}
