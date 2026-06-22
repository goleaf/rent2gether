<?php

namespace App\Services\Reviews;

use App\Models\RatingEvent;

class ReviewDepositIntegrationService
{
    public function __construct(private readonly RatingEventService $ratingEvents) {}

    public function createRatingEventFromConfirmedDeduction(object $deduction): ?RatingEvent
    {
        return $this->ratingEvents->createConfirmedDepositDeductionEvent($deduction);
    }

    public function removeRatingImpactIfDeductionRejected(object $deduction): void
    {
        RatingEvent::query()
            ->where('source_type', 'booking_deposit_deduction')
            ->where('source_id', $deduction->id)
            ->get()
            ->each(fn (RatingEvent $event) => $this->ratingEvents->ignoreEvent($event, 'deposit_deduction_rejected'));
    }
}
