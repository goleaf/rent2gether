<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\BookingCancellationEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCancellationEvent>
 */
class BookingCancellationEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_cancellation_id' => BookingCancellation::factory(),
            'booking_id' => Booking::factory(),
            'event_key' => 'cancellation_confirmed',
            'event_type' => 'system',
            'source_type' => null,
            'source_id' => null,
            'user_id' => null,
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
