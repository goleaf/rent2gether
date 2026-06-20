<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingReviewRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingReviewRequest>
 */
class BookingReviewRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'reviewer_user_id' => User::factory(),
            'reviewee_user_id' => User::factory(),
            'reviewer_role' => 'guest',
            'status' => 'pending',
            'requested_at' => now(),
            'completed_at' => null,
            'expires_at' => now()->addDays(14),
        ];
    }
}
