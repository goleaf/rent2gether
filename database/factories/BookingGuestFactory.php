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
            'guest_name' => $this->faker->name(),
            'guest_email' => $this->faker->safeEmail(),
            'guest_phone' => $this->faker->phoneNumber(),
            'guest_type' => 'main_guest',
            'verification_status' => 'not_required',
            'is_main_guest' => true,
            'full_name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 65),
            'document_type' => null,
            'document_last_four' => null,
        ];
    }
}
