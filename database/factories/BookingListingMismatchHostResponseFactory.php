<?php

namespace Database\Factories;

use App\Models\BookingListingMismatchHostResponse;
use App\Models\BookingListingMismatchReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchHostResponse>
 */
class BookingListingMismatchHostResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_listing_mismatch_report_id' => BookingListingMismatchReport::factory(),
            'host_user_id' => User::factory()->host(),
            'response_type' => fake()->randomElement(['accept', 'offer_fix', 'offer_refund', 'deny']),
            'message' => fake()->sentence(),
            'accepts_problem' => null,
            'proposed_resolution_type' => null,
            'offered_compensation_amount' => null,
            'offered_refund_amount' => null,
            'currency' => 'EUR',
            'alternative_sleeping_place_id' => null,
            'maintenance_request_id' => null,
            'cleaning_task_id' => null,
        ];
    }
}
