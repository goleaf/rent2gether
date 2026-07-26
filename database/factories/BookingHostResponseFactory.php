<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingHostResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingHostResponse>
 */
class BookingHostResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'host_user_id' => User::factory(),
            'response_type' => BookingHostResponse::TYPE_APPROVED,
            'message' => null,
            'proposed_check_in_time' => null,
            'proposed_check_out_time' => null,
            'rejection_reason' => null,
        ];
    }
}
