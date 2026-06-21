<?php

namespace Database\Factories;

use App\Models\BookingRequest;
use App\Models\BookingRequestHostResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequestHostResponse>
 */
class BookingRequestHostResponseFactory extends Factory
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
            'host_user_id' => User::factory()->host(),
            'response_type' => BookingRequestHostResponse::TYPE_ASK_QUESTION,
            'message' => $this->faker->sentence(),
        ];
    }
}
