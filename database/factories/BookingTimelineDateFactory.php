<?php

namespace Database\Factories;

use App\Models\BookingQuote;
use App\Models\BookingTimelineDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingTimelineDate>
 */
class BookingTimelineDateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_quote_id' => BookingQuote::factory(),
            'booking_id' => null,
            'event_key' => 'payment_deadline',
            'scheduled_at' => now()->addMinutes(20),
            'status' => 'pending',
        ];
    }
}
