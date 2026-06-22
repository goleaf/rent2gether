<?php

namespace App\Services\Reviews;

use App\Models\RatingEvent;

class ReviewHostUnresponsiveIntegrationService
{
    public function freezeImpactForOpenCase(object $case): void
    {
        RatingEvent::query()
            ->where('booking_id', $case->booking_id)
            ->update(['frozen' => true]);
    }

    public function releaseImpactAfterCaseClosed(object $case): void
    {
        RatingEvent::query()
            ->where('booking_id', $case->booking_id)
            ->update(['frozen' => false]);
    }
}
