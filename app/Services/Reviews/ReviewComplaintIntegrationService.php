<?php

namespace App\Services\Reviews;

use App\Models\ComplaintCase;
use App\Models\RatingEvent;

class ReviewComplaintIntegrationService
{
    public function __construct(private readonly RatingEventService $ratingEvents) {}

    public function createRatingEventFromConfirmedComplaint(ComplaintCase $case): ?RatingEvent
    {
        return $this->ratingEvents->createConfirmedComplaintEvent($case);
    }

    public function removeRatingImpactIfComplaintRejected(ComplaintCase $case): void
    {
        RatingEvent::query()
            ->where('source_type', 'complaint_case')
            ->where('source_id', $case->id)
            ->get()
            ->each(fn (RatingEvent $event) => $this->ratingEvents->ignoreEvent($event, 'complaint_rejected'));
    }
}
