<?php

namespace App\Services\Complaints;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationPreview;
use App\Models\ComplaintCase;
use App\Services\Bookings\BookingCancellationNumberService;

class ComplaintCancellationIntegrationService
{
    public function __construct(
        private readonly BookingCancellationNumberService $numbers,
        private readonly ComplaintActionService $actions,
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
    ) {}

    public function createCancellationPreviewFromComplaint(ComplaintCase $case): BookingCancellationPreview
    {
        $case->loadMissing('booking');
        $booking = $case->booking;

        return BookingCancellationPreview::query()->create([
            'preview_number' => $this->numbers->generatePreviewNumber(),
            'booking_id' => $booking->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'requested_by_user_id' => $case->reporter_user_id,
            'requested_by_type' => $case->submitted_by_type,
            'cancellation_type' => 'complaint_related',
            'reason_key' => 'complaint',
            'comment' => $case->description,
            'check_in_date' => $booking->check_in_date,
            'check_out_date' => $booking->check_out_date,
            'cancelled_at_preview' => now(),
            'nights_used' => 0,
            'nights_unused' => (int) ($booking->nights_count ?: $booking->nights ?: 0),
            'accommodation_amount' => $booking->accommodation_amount ?: $booking->subtotal_amount ?: 0,
            'cleaning_fee_amount' => $booking->cleaning_fee_amount ?: 0,
            'service_fee_amount' => $booking->service_fee_amount ?: 0,
            'deposit_amount' => $booking->deposit_amount ?: 0,
            'deposit_refund_amount' => $booking->deposit_amount ?: 0,
            'total_refund_amount' => $case->amount_requested ?: 0,
            'currency' => $booking->currency ?: 'EUR',
            'policy_snapshot_json' => ['source' => 'complaint'],
            'refund_breakdown_json' => ['source' => 'complaint', 'complaint_case_id' => $case->id],
            'expires_at' => now()->addMinutes(30),
            'status' => 'calculated',
        ]);
    }

    public function createCancellationFromComplaint(ComplaintCase $case): BookingCancellation
    {
        $preview = $this->createCancellationPreviewFromComplaint($case);
        $case->loadMissing('booking');
        $booking = $case->booking;

        $cancellation = BookingCancellation::query()->create([
            'cancellation_number' => $this->numbers->generateCancellationNumber(),
            'booking_id' => $booking->id,
            'booking_cancellation_preview_id' => $preview->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'cancelled_by_user_id' => $case->reporter_user_id,
            'cancelled_by_type' => $case->submitted_by_type,
            'cancellation_type' => 'complaint_related',
            'reason_key' => 'complaint',
            'comment' => $case->description,
            'status' => 'created',
            'check_in_date' => $booking->check_in_date,
            'check_out_date' => $booking->check_out_date,
            'cancelled_at' => now(),
            'currency' => $booking->currency ?: 'EUR',
            'refund_status' => 'not_created',
            'calendar_release_status' => 'not_released',
            'complaint_case_id' => $case->id,
        ]);

        $case->forceFill([
            'booking_cancellation_id' => $cancellation->id,
            'resolution_type' => 'cancellation',
            'resolution_status' => 'completed',
        ])->save();

        $this->actions->createAction($case->fresh(), 'create_cancellation', ['status' => 'completed', 'source_type' => 'booking_cancellation', 'source_id' => $cancellation->id, 'completed_at' => now()]);
        $this->statuses->transition($case->fresh(), 'cancellation_created');
        $this->events->record($case->fresh(), 'cancellation_created', ['booking_cancellation_id' => $cancellation->id]);

        return $cancellation->fresh();
    }
}
