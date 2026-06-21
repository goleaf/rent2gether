<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingListingMismatchEvent;
use App\Models\BookingListingMismatchReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchEvent>
 */
class BookingListingMismatchEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_listing_mismatch_report_id' => BookingListingMismatchReport::factory(),
            'booking_id' => Booking::factory(),
            'event_key' => fake()->randomElement(['mismatch_reported', 'host_notified', 'resolution_offered']),
            'event_type' => 'system',
            'source_type' => null,
            'source_id' => null,
            'user_id' => null,
            'occurred_at' => now(),
            'context_json' => null,
        ];
    }
}
