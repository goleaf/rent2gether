<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingListingMismatchReport;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingListingMismatchReport>
 */
class BookingListingMismatchReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mismatch_number' => sprintf('MM-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'booking_stay_id' => null,
            'booking_check_in_id' => null,
            'booking_check_out_id' => null,
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory()->host(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'source_type' => 'guest_report',
            'source_id' => null,
            'mismatch_type' => fake()->randomElement(['missing_wifi', 'missing_locker', 'dirty_room', 'photos_mismatch']),
            'severity' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => 'reported',
            'reported_at' => now(),
            'discovered_at' => now(),
            'guest_description' => fake()->sentence(),
            'host_response' => null,
            'what_was_promised' => null,
            'what_was_actual' => null,
            'guest_wants_to_stay' => true,
            'guest_wants_fix' => true,
            'guest_wants_relocation' => false,
            'guest_wants_cancellation' => false,
            'guest_wants_refund' => false,
            'guest_wants_compensation' => false,
            'host_accepts_problem' => null,
            'host_offered_fix' => false,
            'host_offered_relocation' => false,
            'host_offered_refund' => false,
            'host_offered_compensation' => false,
            'host_denied_problem' => false,
            'resolution_type' => null,
            'resolution_status' => 'not_started',
            'compensation_amount' => 0,
            'refund_amount' => 0,
            'price_difference_amount' => 0,
            'currency' => 'EUR',
            'cleaning_task_id' => null,
            'maintenance_request_id' => null,
            'booking_relocation_id' => null,
            'booking_cancellation_id' => null,
            'booking_refund_id' => null,
            'complaint_case_id' => null,
            'snapshot_compared' => false,
            'auto_match_confidence' => null,
            'future_review_required' => false,
            'future_review_comment' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }
}
