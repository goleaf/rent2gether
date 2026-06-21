<?php

namespace Database\Factories;

use App\Models\BookingListingMismatchGuestResponse;
use App\Models\BookingListingMismatchReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchGuestResponse>
 */
class BookingListingMismatchGuestResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_listing_mismatch_report_id' => BookingListingMismatchReport::factory(),
            'guest_user_id' => User::factory(),
            'response_type' => fake()->randomElement(['accept_resolution', 'reject_resolution', 'request_refund']),
            'message' => fake()->sentence(),
            'accepted_resolution_type' => null,
            'accepted_compensation_amount' => null,
            'accepted_refund_amount' => null,
            'accepted_relocation_id' => null,
        ];
    }
}
