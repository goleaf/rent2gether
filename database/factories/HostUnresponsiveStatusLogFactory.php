<?php

namespace Database\Factories;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostUnresponsiveStatusLog>
 */
class HostUnresponsiveStatusLogFactory extends Factory
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
            'booking_id' => \App\Models\Booking::factory(),
            'old_status' => null,
            'new_status' => 'reported',
            'reason_key' => 'host_unresponsive_reported',
            'context_json' => [],
        ];
    }
}
