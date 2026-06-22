<?php

namespace App\Services\Reviews;

use App\Models\Review;

class ReviewSearchIntegrationService
{
    public function __construct(private readonly RatingSearchIntegrationService $ratings) {}

    public function refreshListingRatingAfterReview(Review $review): void
    {
        if ($review->sleepingPlace) {
            $this->ratings->updateSearchScoreForSleepingPlace($review->sleepingPlace);
        }
    }

    public function refreshHostRatingAfterReview(Review $review): void
    {
        if ($review->target_type === 'host' && $review->target) {
            $this->ratings->updateSearchScoreForHost($review->target);
        }
    }
}
