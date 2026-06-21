<?php

namespace Database\Factories;

use App\Models\BookingRequest;
use App\Models\BookingRequestWarning;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequestWarning>
 */
class BookingRequestWarningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = $this->faker->randomElement(['late_night_arrival', 'new_guest', 'last_minute_request']);

        return [
            'booking_request_id' => BookingRequest::factory(),
            'warning_key' => $key,
            'severity' => BookingRequestWarning::SEVERITY_WARNING,
            'message_key' => 'booking_requests.warnings.'.$key,
            'message_params_json' => [],
            'blocking' => false,
            'visible_to_host' => true,
            'visible_to_guest' => false,
        ];
    }
}
