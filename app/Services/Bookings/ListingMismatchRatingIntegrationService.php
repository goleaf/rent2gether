<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;

class ListingMismatchRatingIntegrationService
{
    public function recordConfirmedMismatch(BookingListingMismatchReport $report): void
    {
        if (! in_array((string) $report->status, ['confirmed', 'partially_confirmed', 'refund_created', 'complaint_created', 'relocation_started'], true)) {
            return;
        }

        app(ListingMismatchEventService::class)->record($report, 'mismatch_confirmed', [
            'rating_signal' => $this->ratingSignalFor($report),
        ]);
    }

    public function recordResolvedMismatch(BookingListingMismatchReport $report): void
    {
        app(ListingMismatchEventService::class)->record($report, 'fix_completed', [
            'rating_signal' => 'problem_resolution',
        ]);
    }

    public function removeRatingImpactIfRejected(BookingListingMismatchReport $report): void
    {
        app(ListingMismatchEventService::class)->record($report, 'mismatch_rejected', [
            'rating_signal_removed' => true,
        ]);
    }

    private function ratingSignalFor(BookingListingMismatchReport $report): string
    {
        return match ($report->mismatch_type) {
            'dirty_sleeping_place', 'dirty_room', 'dirty_property', 'bad_smell', 'mold', 'insects' => 'cleanliness',
            'safety_mismatch' => 'safety',
            'missing_wifi', 'missing_locker', 'missing_bedding', 'missing_towel', 'amenity_mismatch' => 'amenities',
            default => 'description_accuracy',
        };
    }
}
