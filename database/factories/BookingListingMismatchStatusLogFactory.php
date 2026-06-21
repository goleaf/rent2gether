<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingListingMismatchReport;
use App\Models\BookingListingMismatchStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchStatusLog>
 */
class BookingListingMismatchStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_listing_mismatch_report_id' => BookingListingMismatchReport::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => null,
            'old_status' => null,
            'new_status' => 'reported',
            'reason_key' => 'listing_mismatch.statuses.reported',
            'note' => null,
            'context_json' => null,
        ];
    }
}
