<?php

namespace App\Services\Reviews;

use App\Models\HostReputationSnapshot;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceRatingSnapshot;
use App\Models\User;

class RatingSearchIntegrationService
{
    public function updateSearchScoreForSleepingPlace(SleepingPlace $place): void
    {
        SleepingPlaceRatingSnapshot::query()
            ->where('sleeping_place_id', $place->id)
            ->first();
    }

    public function updateSearchScoreForHost(User $host): void
    {
        HostReputationSnapshot::query()
            ->where('host_user_id', $host->id)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function buildRatingFilters(array $filters): array
    {
        return [
            'minimum_rating' => $filters['minimum_rating'] ?? null,
            'minimum_reviews_count' => $filters['minimum_reviews_count'] ?? null,
            'requires_verified_host' => (bool) ($filters['requires_verified_host'] ?? false),
        ];
    }
}
