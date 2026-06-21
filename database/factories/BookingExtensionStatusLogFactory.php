<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\BookingExtensionStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingExtensionStatusLog>
 */
class BookingExtensionStatusLogFactory extends Factory
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
            'user_id' => User::factory(),
            'old_status' => 'draft',
            'new_status' => 'quote_created',
            'reason_key' => null,
            'note' => null,
            'context_json' => [],
        ];
    }
}
