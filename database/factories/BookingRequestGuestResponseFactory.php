<?php

namespace Database\Factories;

use App\Models\BookingRequest;
use App\Models\BookingRequestGuestResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequestGuestResponse>
 */
class BookingRequestGuestResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_request_id' => BookingRequest::factory(),
            'guest_user_id' => User::factory(),
            'response_type' => BookingRequestGuestResponse::TYPE_ANSWER_QUESTION,
            'message' => $this->faker->sentence(),
        ];
    }
}
