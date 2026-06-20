<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInAlert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckInAlert>
 */
class BookingCheckInAlertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_check_in_id' => BookingCheckIn::factory(),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'alert_type' => 'check_in_problem',
            'severity' => 'medium',
            'status' => 'open',
            'message_key' => 'check_in.alerts.check_in_problem',
            'message_params_json' => [],
            'resolved_at' => null,
        ];
    }
}
