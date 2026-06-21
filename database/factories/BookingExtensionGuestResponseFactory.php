<?php

namespace Database\Factories;

use App\Models\BookingExtension;
use App\Models\BookingExtensionGuestResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingExtensionGuestResponse>
 */
class BookingExtensionGuestResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_extension_id' => BookingExtension::factory(),
            'guest_user_id' => User::factory(),
            'response_type' => 'send_message',
            'message' => fake()->optional()->sentence(),
            'accepted_new_check_out_date' => null,
            'accepted_new_check_out_time' => null,
        ];
    }
}
