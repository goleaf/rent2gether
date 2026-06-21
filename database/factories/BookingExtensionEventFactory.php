<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\BookingExtensionEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingExtensionEvent>
 */
class BookingExtensionEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_extension_id' => BookingExtension::factory(),
            'booking_id' => Booking::factory(),
            'event_key' => 'extension_requested',
            'event_type' => 'system',
            'source_type' => null,
            'source_id' => null,
            'user_id' => User::factory(),
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
