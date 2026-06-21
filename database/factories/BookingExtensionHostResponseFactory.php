<?php

namespace Database\Factories;

use App\Models\BookingExtension;
use App\Models\BookingExtensionHostResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingExtensionHostResponse>
 */
class BookingExtensionHostResponseFactory extends Factory
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
            'host_user_id' => User::factory(),
            'response_type' => 'approve',
            'message' => fake()->optional()->sentence(),
            'proposed_new_check_out_date' => null,
            'proposed_new_check_out_time' => null,
            'rejection_reason' => null,
        ];
    }
}
