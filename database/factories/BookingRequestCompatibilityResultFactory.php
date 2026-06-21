<?php

namespace Database\Factories;

use App\Models\BookingRequest;
use App\Models\BookingRequestCompatibilityResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequestCompatibilityResult>
 */
class BookingRequestCompatibilityResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = $this->faker->randomElement(['guest_count', 'smoking_policy', 'pet_policy']);

        return [
            'booking_request_id' => BookingRequest::factory(),
            'compatibility_key' => $key,
            'status' => BookingRequestCompatibilityResult::STATUS_GOOD,
            'severity' => 'info',
            'message_key' => 'booking_requests.compatibility.'.$key,
            'message_params_json' => [],
        ];
    }
}
