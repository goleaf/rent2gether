<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostUnresponsiveEvent>
 */
class HostUnresponsiveEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'host_unresponsive_case_id' => BookingHostUnresponsiveCase::factory(),
            'booking_id' => Booking::factory(),
            'event_key' => 'host_unresponsive_reported',
            'event_type' => 'system',
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
