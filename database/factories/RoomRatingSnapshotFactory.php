<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Room;
use App\Models\RoomRatingSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomRatingSnapshot>
 */
class RoomRatingSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'property_id' => Property::factory(),
            'host_user_id' => User::factory(),
            'overall_rating' => 5,
            'cleanliness_rating' => 5,
            'safety_rating' => 5,
            'noise_level_rating' => 5,
            'roommate_experience_rating' => 5,
            'roommate_cleanliness_rating' => 5,
            'roommate_friendliness_rating' => 5,
            'roommate_quietness_rating' => 5,
            'reviews_count' => 1,
            'completed_stays_count' => 1,
            'confirmed_roommate_complaints_count' => 0,
            'confirmed_noise_complaints_count' => 0,
            'last_recalculated_at' => now(),
        ];
    }
}
