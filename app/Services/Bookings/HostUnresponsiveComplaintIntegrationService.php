<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;

class HostUnresponsiveComplaintIntegrationService
{
    public function createComplaintIfUnresolved(BookingHostUnresponsiveCase $case): mixed
    {
        $case->forceFill([
            'complaint_case_id' => $case->complaint_case_id ?: $case->id,
            'future_support_review_required' => true,
        ])->save();

        app(HostUnresponsiveEventService::class)->record($case->fresh(), 'complaint_created');

        return $case->complaint_case_id;
    }

    public function linkComplaint(BookingHostUnresponsiveCase $case, $complaint): void
    {
        $case->forceFill([
            'complaint_case_id' => is_object($complaint) && isset($complaint->id) ? $complaint->id : $complaint,
        ])->save();
    }
}
