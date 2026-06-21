<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutIssue;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckOutIssue>
 */
class BookingCheckOutIssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_check_out_id' => BookingCheckOut::factory(),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'issue_type' => 'damage',
            'severity' => 'medium',
            'status' => 'reported',
            'description' => fake()->sentence(),
            'amount_requested' => null,
            'currency' => 'EUR',
            'guest_response' => null,
            'host_response' => null,
            'source_created_deposit_deduction_id' => null,
            'source_created_maintenance_request_id' => null,
            'source_created_complaint_case_id' => null,
            'source_created_inventory_issue_id' => null,
            'resolved_at' => null,
        ];
    }
}
