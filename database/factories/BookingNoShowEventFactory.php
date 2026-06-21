<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Models\BookingNoShowEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingNoShowEvent>
 */
class BookingNoShowEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_no_show_id' => BookingNoShow::factory(),
            'booking_id' => Booking::factory(),
            'event_key' => 'no_show_watch_started',
            'event_type' => 'system',
            'source_type' => null,
            'source_id' => null,
            'user_id' => User::factory(),
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
