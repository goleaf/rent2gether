<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCheckIn>
 */
class BookingCheckInFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'check_in_date' => now()->toDateString(),
            'planned_check_in_time' => '15:00',
            'planned_check_in_window' => '15:00-22:00',
            'actual_arrival_at' => null,
            'actual_check_in_at' => null,
            'check_in_method' => 'host_meet',
            'met_by_type' => 'host',
            'met_by_name' => null,
            'keys_handed_over' => false,
            'keys_count' => null,
            'door_code_shared' => false,
            'intercom_code_shared' => false,
            'key_safe_code_shared' => false,
            'room_shown' => false,
            'sleeping_place_shown' => false,
            'rules_explained' => false,
            'kitchen_rules_explained' => false,
            'bathroom_rules_explained' => false,
            'quiet_rules_explained' => false,
            'bedding_given' => false,
            'towel_given' => false,
            'locker_given' => false,
            'locker_key_given' => false,
            'guest_confirmed_at' => null,
            'host_confirmed_at' => null,
            'has_problem' => false,
            'problem_status' => null,
            'status' => 'not_started',
            'last_reminder_sent_at' => null,
        ];
    }
}
