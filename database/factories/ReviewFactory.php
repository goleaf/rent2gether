<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'type' => ReviewType::GuestToPlace->value,
            'bed_id' => Bed::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'room_id' => Room::factory(),
            'property_id' => Property::factory(),
            'overall_rating' => 5,
            'cleanliness_rating' => 5,
            'safety_rating' => 5,
            'location_rating' => 5,
            'accuracy_rating' => 5,
            'bed_comfort_rating' => 5,
            'sleeping_place_comfort_rating' => 5,
            'amenities_rating' => 5,
            'communication_rating' => 5,
            'host_communication_rating' => 5,
            'neighbors_rating' => 5,
            'value_rating' => 5,
            'rule_compliance_rating' => 5,
            'rule_following_rating' => 5,
            'tidiness_rating' => 5,
            'punctuality_rating' => 5,
            'respect_rating' => 5,
            'positive_comment' => $this->faker->sentence(),
            'negative_comment' => null,
            'advice' => null,
            'would_recommend' => true,
            'would_return' => true,
            'liked_text' => $this->faker->sentence(),
            'improvement_text' => null,
            'advice_text' => null,
            'comment' => null,
            'photos_json' => [],
            'recommend' => true,
            'recommend_guest' => null,
            'status' => ReviewStatus::Published->value,
            'visible_at' => now(),
            'flagged_words_json' => [],
        ];
    }
}
