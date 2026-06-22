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
            'review_number' => sprintf('REV-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'review_request_id' => null,
            'booking_stay_id' => null,
            'booking_check_out_id' => null,
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'author_user_id' => User::factory(),
            'author_type' => 'guest',
            'target_user_id' => User::factory(),
            'target_type' => 'sleeping_place',
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'type' => ReviewType::GuestToPlace->value,
            'review_subject_type' => 'sleeping_place',
            'bed_id' => Bed::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'room_id' => Room::factory(),
            'property_id' => Property::factory(),
            'overall_rating' => 5,
            'title' => $this->faker->sentence(3),
            'public_comment' => $this->faker->sentence(),
            'private_comment' => null,
            'what_liked' => $this->faker->sentence(),
            'what_disliked' => null,
            'advice_to_future_guests' => null,
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
            'is_public' => true,
            'is_anonymous_future' => false,
            'is_double_blind' => true,
            'is_published_after_window' => false,
            'submitted_at' => now(),
            'published_at' => now(),
            'hidden_at' => null,
            'expired_at' => null,
            'edited_at' => null,
            'edit_deadline_at' => now()->addDay(),
            'edit_count' => 0,
            'language_locale' => 'en',
            'visible_at' => now(),
            'flagged_words_json' => [],
        ];
    }
}
