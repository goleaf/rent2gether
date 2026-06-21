<?php

namespace Database\Factories;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveGuestAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostUnresponsiveGuestAction>
 */
class HostUnresponsiveGuestActionFactory extends Factory
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
            'guest_user_id' => User::factory(),
            'action_type' => 'reported_host_not_answering',
            'message' => $this->faker->sentence(),
        ];
    }
}
