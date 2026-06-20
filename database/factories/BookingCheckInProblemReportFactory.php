<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInProblemReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckInProblemReport>
 */
class BookingCheckInProblemReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_in_id' => BookingCheckIn::factory(),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'problem_type' => 'cannot_enter',
            'severity' => 'medium',
            'description' => $this->faker->sentence(),
            'photo_paths_json' => [],
            'status' => 'open',
            'host_response' => null,
            'resolved_at' => null,
        ];
    }
}
