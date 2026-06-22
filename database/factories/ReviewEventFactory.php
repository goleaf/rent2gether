<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Review;
use App\Models\ReviewEvent;
use App\Models\ReviewRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewEvent>
 */
class ReviewEventFactory extends Factory
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
            'review_request_id' => ReviewRequest::factory(),
            'booking_id' => Booking::factory(),
            'event_key' => 'review_submitted',
            'event_type' => 'system',
            'source_type' => 'review',
            'source_id' => null,
            'user_id' => User::factory(),
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
