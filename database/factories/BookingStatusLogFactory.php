<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingStatusLog>
 */
class BookingStatusLogFactory extends Factory
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
            'user_id' => User::factory(),
            'old_status' => BookingStatus::Created->value,
            'new_status' => BookingStatus::Confirmed->value,
            'reason_key' => 'bookings.lifecycle.transitioned',
            'note' => null,
            'context_json' => [],
        ];
    }
}
