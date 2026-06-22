<?php

namespace App\Services\Reviews;

use App\Models\DisputeCase;
use App\Models\RatingEvent;

class ReviewDisputeIntegrationService
{
    public function __construct(private readonly RatingEventService $ratingEvents) {}

    public function freezeRatingImpactForDispute(DisputeCase $dispute): void
    {
        RatingEvent::query()
            ->where('booking_id', $dispute->booking_id)
            ->get()
            ->each(fn (RatingEvent $event) => $this->ratingEvents->freezeEvent($event));
    }

    public function releaseRatingImpactAfterDispute(DisputeCase $dispute): void
    {
        RatingEvent::query()
            ->where('booking_id', $dispute->booking_id)
            ->get()
            ->each(fn (RatingEvent $event) => $this->ratingEvents->unfreezeEvent($event));
    }
}
