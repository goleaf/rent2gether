<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInProblem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckInProblem>
 */
class BookingCheckInProblemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_in_id' => BookingCheckIn::factory(),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'problem_type' => 'other',
            'severity' => 'medium',
            'status' => 'reported',
            'description' => 'Demo check-in problem.',
            'guest_wants_help' => true,
            'guest_wants_relocation' => false,
            'guest_wants_cancellation' => false,
            'guest_wants_refund' => false,
            'host_response' => null,
            'source_created_host_unresponsive_case_id' => null,
            'source_created_complaint_case_id' => null,
            'source_created_mismatch_report_id' => null,
            'source_created_maintenance_request_id' => null,
            'resolved_at' => null,
        ];
    }
}
