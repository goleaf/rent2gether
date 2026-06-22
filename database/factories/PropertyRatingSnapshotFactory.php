<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyRatingSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyRatingSnapshot>
 */
class PropertyRatingSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'host_user_id' => User::factory(),
            'overall_rating' => 5,
            'cleanliness_rating' => 5,
            'safety_rating' => 5,
            'location_rating' => 5,
            'kitchen_rating' => 5,
            'bathroom_rating' => 5,
            'internet_rating' => 5,
            'amenities_rating' => 5,
            'description_accuracy_rating' => 5,
            'problem_resolution_rating' => 5,
            'reviews_count' => 1,
            'completed_stays_count' => 1,
            'confirmed_property_complaints_count' => 0,
            'last_recalculated_at' => now(),
        ];
    }
}
