<?php

namespace App\Services\Bookings;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Models\BookingListingMismatchReport;
use App\Models\Complaint;

class ListingMismatchComplaintIntegrationService
{
    public function createComplaintIfSeriousOrUnresolved(BookingListingMismatchReport $report): mixed
    {
        if (! in_array($report->severity, ['high', 'urgent', 'unsafe'], true) && ! in_array((string) $report->status, ['dispute_opened', 'rejected'], true)) {
            return null;
        }

        $type = match ($report->mismatch_type) {
            'dirty_sleeping_place', 'dirty_room', 'dirty_property', 'bad_smell', 'mold', 'insects' => ComplaintType::DirtyRoom,
            'safety_mismatch' => ComplaintType::UnsafeSituation,
            'missing_wifi', 'missing_locker', 'missing_bedding', 'missing_towel' => ComplaintType::MissingAmenity,
            default => ComplaintType::Mismatch,
        };

        $complaint = Complaint::query()->create([
            'reporter_user_id' => $report->guest_user_id,
            'reporter_id' => $report->guest_user_id,
            'reported_user_id' => $report->host_user_id,
            'booking_id' => $report->booking_id,
            'property_id' => $report->property_id,
            'room_id' => $report->room_id,
            'bed_id' => null,
            'sleeping_place_id' => $report->sleeping_place_id,
            'type' => $type,
            'priority' => $report->severity,
            'urgency' => $report->severity,
            'description' => $report->guest_description,
            'desired_resolution' => $report->resolution_type ?: 'refund_review',
            'refund_requested' => (bool) $report->guest_wants_refund,
            'deposit_hold_requested' => false,
            'status' => ComplaintStatus::Created,
        ]);

        $this->linkComplaint($report, $complaint);

        return $complaint->fresh();
    }

    public function linkComplaint(BookingListingMismatchReport $report, mixed $complaint): void
    {
        $report->forceFill([
            'complaint_case_id' => $complaint->id,
            'status' => 'complaint_created',
        ])->save();

        app(ListingMismatchEventService::class)->record($report->fresh(), 'complaint_created', ['complaint_case_id' => $complaint->id]);
    }

    public function markComplaintResolutionOffered(BookingListingMismatchReport $report): void
    {
        $report->forceFill(['resolution_status' => 'offered'])->save();
    }
}
