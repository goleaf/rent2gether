<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingNoShow;
use App\Models\BookingNoShowStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingNoShowStatusLog>
 */
class BookingNoShowStatusLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_no_show_id' => BookingNoShow::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'old_status' => null,
            'new_status' => 'watching',
            'reason_key' => 'no_show.events.no_show_watch_started',
            'note' => null,
            'context_json' => [],
        ];
    }
}
