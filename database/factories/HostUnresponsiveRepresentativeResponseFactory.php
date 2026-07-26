<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostRepresentative;
use App\Models\HostUnresponsiveRepresentativeResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostUnresponsiveRepresentativeResponse>
 */
class HostUnresponsiveRepresentativeResponseFactory extends Factory
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
            'booking_id' => Booking::factory(),
            'host_representative_id' => HostRepresentative::factory(),
            'representative_user_id' => User::factory(),
            'response_type' => 'i_can_help',
            'message' => $this->faker->sentence(),
            'will_meet_guest' => true,
            'access_help_provided' => false,
            'keys_handed_over' => false,
            'guest_checked_in' => false,
        ];
    }
}
