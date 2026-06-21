<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckOutStatusLog>
 */
class BookingCheckOutStatusLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_check_out_id' => BookingCheckOut::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'old_status' => 'scheduled',
            'new_status' => 'guest_checked_out',
            'reason_key' => null,
            'note' => null,
            'context_json' => [],
        ];
    }
}
