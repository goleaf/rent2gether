<?php

namespace Database\Factories;

use App\Models\RatingAggregate;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RatingAggregate>
 */
class RatingAggregateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'target_type' => 'sleeping_place',
            'target_user_id' => null,
            'property_id' => null,
            'room_id' => null,
            'sleeping_place_id' => SleepingPlace::factory(),
            'metric_key' => 'overall',
            'rating_average' => 5,
            'rating_weighted_average' => 5,
            'rating_count' => 1,
            'rating_sum' => 5,
            'rating_weight_sum' => 1,
            'last_review_id' => null,
            'last_rating_event_id' => null,
            'last_recalculated_at' => now(),
        ];
    }
}
