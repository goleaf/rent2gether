<?php

namespace Database\Factories;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveHostResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostUnresponsiveHostResponse>
 */
class HostUnresponsiveHostResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'host_unresponsive_case_id' => BookingHostUnresponsiveCase::factory(),
            'booking_id' => \App\Models\Booking::factory(),
            'host_user_id' => User::factory()->host(),
            'response_type' => 'i_am_available',
            'message' => $this->faker->sentence(),
            'instruction_resent' => false,
            'access_details_provided' => false,
            'representative_assigned' => false,
        ];
    }
}
