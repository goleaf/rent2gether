<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\ReviewResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewResponse>
 */
class ReviewResponseFactory extends Factory
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
            'responder_user_id' => User::factory(),
            'responder_type' => 'host',
            'status' => 'submitted',
            'response_text' => $this->faker->sentence(),
            'is_public' => true,
            'submitted_at' => now(),
            'published_at' => null,
            'hidden_at' => null,
        ];
    }
}
