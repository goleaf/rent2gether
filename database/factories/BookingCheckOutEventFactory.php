<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckOutEvent>
 */
class BookingCheckOutEventFactory extends Factory
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
            'event_key' => 'guest_confirmed_checkout',
            'event_type' => 'system',
            'source_type' => null,
            'source_id' => null,
            'user_id' => User::factory(),
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
