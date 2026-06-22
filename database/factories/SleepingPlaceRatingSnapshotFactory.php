<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceRatingSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceRatingSnapshot>
 */
class SleepingPlaceRatingSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'room_id' => Room::factory(),
            'property_id' => Property::factory(),
            'host_user_id' => User::factory(),
            'overall_rating' => 5,
            'cleanliness_rating' => 5,
            'safety_rating' => 5,
            'location_rating' => 5,
            'description_accuracy_rating' => 5,
            'sleeping_place_quality_rating' => 5,
            'mattress_quality_rating' => 5,
            'noise_level_rating' => 5,
            'amenities_rating' => 5,
            'internet_rating' => 5,
            'value_for_money_rating' => 5,
            'problem_resolution_rating' => 5,
            'reviews_count' => 1,
            'published_reviews_count' => 1,
            'photo_reviews_count' => 0,
            'completed_stays_count' => 1,
            'confirmed_mismatch_count' => 0,
            'confirmed_maintenance_issues_count' => 0,
            'confirmed_cleanliness_complaints_count' => 0,
            'last_review_at' => now(),
            'last_recalculated_at' => now(),
        ];
    }
}
