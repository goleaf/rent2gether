<?php

namespace Database\Factories;

use App\Models\BookingListingMismatchResolutionOption;
use App\Models\BookingListingMismatchReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchResolutionOption>
 */
class BookingListingMismatchResolutionOptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_listing_mismatch_report_id' => BookingListingMismatchReport::factory(),
            'resolution_type' => fake()->randomElement(['fix_problem', 'partial_refund', 'relocation']),
            'status' => 'offered',
            'description' => fake()->sentence(),
            'amount' => fake()->randomElement([null, 10, 20, 30]),
            'currency' => 'EUR',
            'sleeping_place_id' => null,
            'booking_relocation_id' => null,
            'booking_cancellation_id' => null,
            'booking_refund_id' => null,
            'cleaning_task_id' => null,
            'maintenance_request_id' => null,
            'offered_by_user_id' => null,
            'accepted_by_user_id' => null,
            'offered_at' => now(),
            'accepted_at' => null,
            'rejected_at' => null,
            'completed_at' => null,
        ];
    }
}
