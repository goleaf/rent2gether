<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Review;
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
            'type' => ReviewType::GuestToPlace->value,
            'bed_id' => Bed::factory(),
            'overall_rating' => $this->faker->numberBetween(3, 5),
            'cleanliness_rating' => $this->faker->numberBetween(3, 5),
            'safety_rating' => $this->faker->numberBetween(3, 5),
            'communication_rating' => $this->faker->numberBetween(3, 5),
            'value_rating' => $this->faker->numberBetween(3, 5),
            'positive_comment' => $this->faker->sentence(),
            'would_recommend' => true,
            'status' => ReviewStatus::Published->value,
        ];
    }

    public function hostToGuest(): static
    {
        return $this->state(fn () => [
            'type' => ReviewType::HostToGuest->value,
            'bed_id' => null,
            'rule_compliance_rating' => $this->faker->numberBetween(3, 5),
            'tidiness_rating' => $this->faker->numberBetween(3, 5),
            'punctuality_rating' => $this->faker->numberBetween(3, 5),
        ]);
    }
}
