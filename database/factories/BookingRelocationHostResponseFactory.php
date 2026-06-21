<?php

namespace Database\Factories;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationHostResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocationHostResponse>
 */
class BookingRelocationHostResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_relocation_id' => BookingRelocation::factory(),
            'host_user_id' => User::factory()->host(),
            'response_type' => 'approve',
            'message' => null,
            'alternative_sleeping_place_id' => null,
            'alternative_room_id' => null,
            'proposed_relocation_date' => null,
            'proposed_relocation_time' => null,
            'rejection_reason' => null,
        ];
    }
}
