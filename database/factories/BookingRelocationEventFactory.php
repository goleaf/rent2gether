<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingRelocation;
use App\Models\BookingRelocationEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocationEvent>
 */
class BookingRelocationEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_relocation_id' => BookingRelocation::factory(),
            'original_booking_id' => Booking::factory(),
            'new_booking_id' => null,
            'event_key' => 'relocation_requested',
            'event_type' => 'system',
            'source_type' => null,
            'source_id' => null,
            'user_id' => null,
            'occurred_at' => now(),
            'context_json' => null,
        ];
    }
}
