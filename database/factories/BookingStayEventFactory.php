<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingStay;
use App\Models\BookingStayEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingStayEvent>
 */
class BookingStayEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_stay_id' => BookingStay::factory(),
            'booking_id' => Booking::factory(),
            'event_key' => 'stay_started',
            'event_type' => 'system',
            'source_type' => null,
            'source_id' => null,
            'user_id' => User::factory(),
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
