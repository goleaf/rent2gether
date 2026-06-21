<?php

namespace Database\Factories;

use App\Models\BookingListingMismatchItem;
use App\Models\BookingListingMismatchReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchItem>
 */
class BookingListingMismatchItemFactory extends Factory
{
    public function definition(): array
    {
        $itemKey = fake()->randomElement(['has_wifi', 'has_locker', 'has_bedding', 'bed_type', 'photo_accuracy']);

        return [
            'booking_listing_mismatch_report_id' => BookingListingMismatchReport::factory(),
            'item_key' => $itemKey,
            'item_type' => fake()->randomElement(['sleeping_place_feature', 'property_amenity', 'photo', 'cleanliness']),
            'promised_value' => 'listed',
            'actual_value' => 'missing',
            'snapshot_source_type' => 'booking_listing_snapshot',
            'snapshot_source_id' => null,
            'is_confirmed' => null,
            'confidence_score' => null,
            'severity' => fake()->randomElement(['low', 'medium', 'high']),
            'guest_note' => fake()->sentence(),
            'host_note' => null,
        ];
    }
}
