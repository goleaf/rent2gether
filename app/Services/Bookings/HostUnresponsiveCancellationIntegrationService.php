<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationPreview;
use App\Models\BookingHostUnresponsiveCase;

class HostUnresponsiveCancellationIntegrationService
{
    public function createCancellationPreview(BookingHostUnresponsiveCase $case): BookingCancellationPreview
    {
        $case->loadMissing('guest', 'booking');

        return app(BookingCancellationPreviewService::class)->createPreview($case->guest, $case->booking, [
            'requested_by_type' => 'guest',
            'cancellation_type' => 'host_unresponsive_related',
            'reason_key' => 'host_unresponsive',
            'comment' => $case->guest_comment,
        ]);
    }

    public function createCancellation(BookingHostUnresponsiveCase $case): BookingCancellation
    {
        $preview = $this->createCancellationPreview($case);
        $cancellation = app(BookingCancellationService::class)->confirmCancellation($case->guest()->firstOrFail(), $preview);

        $case->forceFill([
            'booking_cancellation_id' => $cancellation->id,
            'refund_status' => $cancellation->refund_status,
            'refund_amount' => $cancellation->total_refund_amount,
            'booking_refund_id' => $cancellation->booking_refund_id,
        ])->save();

        app(HostUnresponsiveEventService::class)->record($case->fresh(), 'cancellation_created', ['cancellation_id' => $cancellation->id]);

        return $cancellation;
    }

    public function applyGuestFriendlyCancellationRules(BookingHostUnresponsiveCase $case): void
    {
        $case->forceFill([
            'guest_wants_refund' => true,
            'refund_status' => 'review_started',
        ])->save();
    }
}
