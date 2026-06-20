<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutIssueReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckOutIssueReport>
 */
class BookingCheckOutIssueReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_out_id' => BookingCheckOut::factory(),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'issue_type' => 'damage',
            'severity' => 'medium',
            'description' => $this->faker->sentence(),
            'photo_paths_json' => [],
            'status' => 'open',
            'deposit_related' => false,
            'repair_needed' => false,
            'cleaning_needed' => false,
            'host_response' => null,
            'guest_response' => null,
            'resolved_at' => null,
        ];
    }
}
