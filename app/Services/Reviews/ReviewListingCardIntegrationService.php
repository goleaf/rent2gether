<?php

namespace App\Services\Reviews;

use App\Models\SleepingPlace;

class ReviewListingCardIntegrationService
{
    public function __construct(private readonly SleepingPlaceRatingService $ratings) {}

    /**
     * @return array<string, mixed>
     */
    public function buildCardRating(SleepingPlace $place): array
    {
        return $this->ratings->getCardRating($place);
    }
}
