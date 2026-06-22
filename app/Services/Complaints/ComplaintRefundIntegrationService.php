<?php

namespace App\Services\Complaints;

use App\Models\BookingRefund;
use App\Models\ComplaintCase;
use App\Services\Bookings\BookingRefundService;

class ComplaintRefundIntegrationService
{
    public function __construct(
        private readonly BookingRefundService $refunds,
        private readonly ComplaintActionService $actions,
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
    ) {}

    public function createRefundFromComplaint(ComplaintCase $case, float|int|string $amount): BookingRefund
    {
        $case->loadMissing('booking');
        $refund = $this->refunds->createRefund($case->booking, $amount, 'partial_refund', [
            'reason_key' => 'complaint',
            'source_type' => 'complaint_case',
            'source_id' => $case->id,
        ]);

        $case->forceFill([
            'booking_refund_id' => $refund->id,
            'resolution_type' => 'partial_refund',
            'resolution_status' => 'completed',
        ])->save();

        $this->actions->createAction($case->fresh(), 'create_refund', ['status' => 'completed', 'source_type' => 'booking_refund', 'source_id' => $refund->id, 'completed_at' => now()]);
        $this->statuses->transition($case->fresh(), 'refund_created');
        $this->events->record($case->fresh(), 'refund_created', ['booking_refund_id' => $refund->id]);

        return $refund->fresh();
    }

    public function syncRefundStatus(ComplaintCase $case): void
    {
        if ($case->booking_refund_id) {
            $this->statuses->transition($case, 'refund_created');
        }
    }
}
