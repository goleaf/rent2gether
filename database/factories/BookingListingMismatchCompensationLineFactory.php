<?php

namespace Database\Factories;

use App\Models\BookingListingMismatchCompensationLine;
use App\Models\BookingListingMismatchReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchCompensationLine>
 */
class BookingListingMismatchCompensationLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_listing_mismatch_report_id' => BookingListingMismatchReport::factory(),
            'line_type' => fake()->randomElement(['partial_refund', 'inconvenience_compensation', 'price_difference_refund']),
            'label_key' => 'listing_mismatch.compensation_lines.partial_refund',
            'amount' => fake()->randomFloat(2, 5, 50),
            'currency' => 'EUR',
            'calculation_type' => 'fixed',
            'percent' => null,
            'nights_count' => null,
            'refundable' => true,
            'payable_to_guest' => true,
            'deduct_from_host_payout' => false,
            'reason_key' => 'listing_mismatch',
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
