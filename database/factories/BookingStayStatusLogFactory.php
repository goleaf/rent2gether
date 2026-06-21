<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingStay;
use App\Models\BookingStayStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingStayStatusLog>
 */
class BookingStayStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_stay_id' => BookingStay::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'old_status' => 'not_started',
            'new_status' => 'active',
            'reason_key' => 'stays.events.stay_started',
            'note' => null,
            'context_json' => [],
        ];
    }
}
