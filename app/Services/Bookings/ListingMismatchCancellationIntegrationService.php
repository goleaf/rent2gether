<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationPreview;
use App\Models\BookingListingMismatchReport;

class ListingMismatchCancellationIntegrationService
{
    public function __construct(
        private readonly BookingCancellationPreviewService $previews,
        private readonly BookingCancellationService $cancellations,
        private readonly ListingMismatchEventService $events,
        private readonly ListingMismatchNotificationService $notifications,
    ) {}

    public function createCancellationPreview(BookingListingMismatchReport $report): BookingCancellationPreview
    {
        $report->loadMissing('booking', 'guest');

        return $this->previews->createPreview($report->guest, $report->booking, [
            'requested_by_type' => 'guest',
            'cancellation_type' => 'listing_mismatch_related',
            'reason_key' => 'listing_mismatch',
            'comment' => $report->guest_description,
        ]);
    }

    public function createCancellationFromMismatch(BookingListingMismatchReport $report): BookingCancellation
    {
        $preview = $this->createCancellationPreview($report);
        $cancellation = $this->cancellations->confirmCancellation($report->guest, $preview);

        $report->forceFill([
            'booking_cancellation_id' => $cancellation->id,
            'status' => 'cancellation_started',
            'resolution_type' => 'cancellation',
            'resolution_status' => 'completed',
        ])->save();

        $this->events->record($report->fresh(), 'cancellation_created', ['booking_cancellation_id' => $cancellation->id]);
        $this->notifications->notifyCancellationCreated($report->fresh());

        return $cancellation->fresh();
    }

    public function applyGuestFriendlyCancellationIfConfirmed(BookingListingMismatchReport $report): void
    {
        if (in_array((string) $report->status, ['confirmed', 'partially_confirmed'], true)) {
            $report->forceFill([
                'guest_wants_cancellation' => true,
                'resolution_type' => 'cancellation',
            ])->save();
        }
    }
}
