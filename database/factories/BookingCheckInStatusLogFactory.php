<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckInStatusLog>
 */
class BookingCheckInStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_in_id' => BookingCheckIn::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => null,
            'old_status' => null,
            'new_status' => 'scheduled',
            'reason_key' => 'check_in.events.created',
            'note' => null,
            'context_json' => [],
        ];
    }

    public function byUser(): static
    {
        return $this->state(fn (): array => [
            'user_id' => User::factory(),
        ]);
    }
}
