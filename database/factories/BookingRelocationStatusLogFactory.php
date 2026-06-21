<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingRelocation;
use App\Models\BookingRelocationStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRelocationStatusLog>
 */
class BookingRelocationStatusLogFactory extends Factory
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
            'user_id' => null,
            'old_status' => null,
            'new_status' => 'requested',
            'reason_key' => null,
            'note' => null,
            'context_json' => null,
        ];
    }
}
