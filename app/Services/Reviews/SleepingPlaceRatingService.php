<?php

namespace App\Services\Reviews;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceRatingSnapshot;

class SleepingPlaceRatingService
{
    public function __construct(private readonly RatingSnapshotService $snapshots) {}

    public function getOrCreate(SleepingPlace $place): SleepingPlaceRatingSnapshot
    {
        return SleepingPlaceRatingSnapshot::query()->firstOrCreate(
            ['sleeping_place_id' => $place->id],
            [
                'room_id' => $place->room_id,
                'property_id' => $place->property_id,
                'host_user_id' => $place->property?->host_user_id ?: $place->property?->user_id ?: $place->user_id,
                'last_recalculated_at' => now(),
            ],
        );
    }

    public function refresh(SleepingPlace $place): SleepingPlaceRatingSnapshot
    {
        return $this->snapshots->recalculateSleepingPlace($place);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCardRating(SleepingPlace $place): array
    {
        $snapshot = $this->getOrCreate($place);

        return [
            'overall_rating' => (float) $snapshot->overall_rating,
            'reviews_count' => $snapshot->reviews_count,
            'cleanliness_rating' => (float) $snapshot->cleanliness_rating,
            'safety_rating' => (float) $snapshot->safety_rating,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFullPageRating(SleepingPlace $place): array
    {
        $snapshot = $this->getOrCreate($place);

        return [
            'overall_rating' => (float) $snapshot->overall_rating,
            'reviews_count' => $snapshot->reviews_count,
            'cleanliness_rating' => (float) $snapshot->cleanliness_rating,
            'safety_rating' => (float) $snapshot->safety_rating,
            'internet_rating' => (float) $snapshot->internet_rating,
            'noise_level_rating' => (float) $snapshot->noise_level_rating,
            'value_for_money_rating' => (float) $snapshot->value_for_money_rating,
        ];
    }
}
