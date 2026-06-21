<?php

namespace Database\Factories;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationGuestResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocationGuestResponse>
 */
class BookingRelocationGuestResponseFactory extends Factory
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
            'guest_user_id' => User::factory(),
            'response_type' => 'accept',
            'message' => null,
            'selected_option_id' => null,
            'accepted_sleeping_place_id' => null,
            'accepted_relocation_date' => null,
            'accepted_relocation_time' => null,
        ];
    }
}
