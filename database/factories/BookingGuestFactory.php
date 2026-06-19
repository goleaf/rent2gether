<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingGuest>
 */
class BookingGuestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'full_name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 65),
            'document_type' => null,
            'document_last_four' => null,
        ];
    }
}
