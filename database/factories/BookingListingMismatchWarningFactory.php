<?php

namespace Database\Factories;

use App\Models\BookingListingMismatchReport;
use App\Models\BookingListingMismatchWarning;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchWarning>
 */
class BookingListingMismatchWarningFactory extends Factory
{
    public function definition(): array
    {
        $warningKey = fake()->randomElement(['photo_evidence_missing', 'mismatch_may_require_refund']);

        return [
            'booking_listing_mismatch_report_id' => BookingListingMismatchReport::factory(),
            'warning_key' => $warningKey,
            'severity' => 'warning',
            'message_key' => 'listing_mismatch.warnings.'.$warningKey,
            'message_params_json' => null,
            'visible_to_guest' => true,
            'visible_to_host' => true,
            'blocking' => false,
        ];
    }
}
