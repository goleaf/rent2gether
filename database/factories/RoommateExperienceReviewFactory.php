<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoommateExperienceReview;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoommateExperienceReview>
 */
class RoommateExperienceReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'booking_id' => Booking::factory(),
            'room_id' => Room::factory(),
            'property_id' => Property::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'quiet_roommates' => true,
            'clean_roommates' => true,
            'friendly_roommates' => true,
            'roommates_disturbed_sleep' => false,
            'roommates_broke_rules' => false,
            'conflict_happened' => false,
            'roommate_experience_rating' => 5,
            'comment' => $this->faker->sentence(),
        ];
    }
}
