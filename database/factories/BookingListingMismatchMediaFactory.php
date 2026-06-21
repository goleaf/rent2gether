<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingListingMismatchMedia;
use App\Models\BookingListingMismatchReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchMedia>
 */
class BookingListingMismatchMediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_listing_mismatch_report_id' => BookingListingMismatchReport::factory(),
            'booking_id' => Booking::factory(),
            'uploaded_by_user_id' => User::factory(),
            'media_type' => fake()->randomElement(['photo', 'screenshot']),
            'media_role' => fake()->randomElement(['guest_real_photo', 'missing_amenity_evidence', 'dirty_place_evidence']),
            'path' => 'mismatch/'.fake()->uuid().'.jpg',
            'thumbnail_path' => null,
            'caption' => fake()->sentence(),
            'visibility' => 'guest_and_host',
            'related_mismatch_item_id' => null,
        ];
    }
}
