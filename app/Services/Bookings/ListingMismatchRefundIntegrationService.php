<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;
use App\Models\BookingRefund;

class ListingMismatchRefundIntegrationService
{
    public function __construct(
        private readonly BookingRefundService $refunds,
        private readonly ListingMismatchEventService $events,
        private readonly ListingMismatchNotificationService $notifications,
    ) {}

    public function createRefundIfAccepted(BookingListingMismatchReport $report): ?BookingRefund
    {
        if (! in_array((string) $report->resolution_status, ['accepted', 'completed'], true) && ! in_array((string) $report->status, ['confirmed', 'partially_confirmed'], true)) {
            return null;
        }

        $amount = (float) ($report->refund_amount ?: $report->compensation_amount);

        if ($amount <= 0) {
            return null;
        }

        return $this->createPartialRefund($report, $amount);
    }

    public function createPartialRefund(BookingListingMismatchReport $report, float|int|string $amount): BookingRefund
    {
        $report->loadMissing('booking');
        $refund = $this->refunds->createRefund($report->booking, $amount, 'partial_refund', [
            'reason_key' => 'listing_mismatch',
            'source_type' => 'listing_mismatch_report',
            'source_id' => $report->id,
        ]);

        $report->forceFill([
            'booking_refund_id' => $refund->id,
            'refund_amount' => $amount,
            'status' => 'refund_created',
            'resolution_status' => 'completed',
        ])->save();

        app(ListingMismatchCompensationService::class)->createCompensationLines($report->fresh(), $amount);
        $this->events->record($report->fresh(), 'refund_created', ['booking_refund_id' => $refund->id]);
        $this->notifications->notifyRefundCreated($report->fresh());

        return $refund->fresh();
    }

    public function syncRefundStatus(BookingListingMismatchReport $report): void
    {
        if ($report->booking_refund_id) {
            $report->forceFill(['status' => 'refund_created'])->save();
        }
    }
}
