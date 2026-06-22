<?php

namespace App\Services\Reviews;

use App\Models\BookingNoShow;
use App\Models\RatingEvent;

class ReviewNoShowIntegrationService
{
    public function __construct(private readonly RatingEventService $ratingEvents) {}

    public function createGuestNoShowRatingEvent(BookingNoShow $noShow): ?RatingEvent
    {
        return $this->ratingEvents->createConfirmedNoShowEvent($noShow);
    }

    public function removeNoShowRatingImpactIfRejected(BookingNoShow $noShow): void
    {
        RatingEvent::query()
            ->where('source_type', 'booking_no_show')
            ->where('source_id', $noShow->id)
            ->get()
            ->each(fn (RatingEvent $event) => $this->ratingEvents->ignoreEvent($event, 'no_show_rejected'));
    }
}
