<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\BookingCancellationStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCancellationStatusLog>
 */
class BookingCancellationStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_cancellation_id' => BookingCancellation::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => null,
            'old_status' => null,
            'new_status' => 'booking_cancelled',
            'reason_key' => null,
            'note' => null,
            'context_json' => [],
        ];
    }
}
