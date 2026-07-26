<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingLifecycleEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingLifecycleEvent>
 */
class BookingLifecycleEventFactory extends Factory
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
            'event_key' => 'created',
            'event_type' => 'system',
            'source_type' => null,
            'source_id' => null,
            'user_id' => User::factory(),
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
