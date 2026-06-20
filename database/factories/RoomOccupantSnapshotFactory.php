<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomOccupantSnapshot;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomOccupantSnapshot>
 */
class RoomOccupantSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = $this->faker->dateTimeBetween('+1 week', '+2 months');
        $checkOut = (clone $checkIn)->modify('+'.random_int(2, 14).' days');

        return [
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'status' => RoomOccupantSnapshot::STATUS_UPCOMING,
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'public_alias_snapshot' => $this->faker->firstName(),
            'age_range_snapshot' => $this->faker->randomElement(['18-24', '25-34', '35-44']),
            'gender_for_room_policy_snapshot' => null,
            'country_label_snapshot' => null,
            'city_label_snapshot' => null,
            'languages_json_snapshot' => ['en'],
            'stay_purpose_snapshot' => 'work',
            'guest_type_snapshot' => 'short_term_guest',
            'tourist_snapshot' => false,
            'student_snapshot' => false,
            'working_snapshot' => true,
            'remote_worker_snapshot' => false,
            'long_term_guest_snapshot' => false,
            'short_term_guest_snapshot' => true,
            'sleep_schedule_snapshot' => 'normal',
            'wake_schedule_snapshot' => null,
            'home_presence_level_snapshot' => 'balanced',
            'smokes_snapshot' => false,
            'social_level_snapshot' => 'calm',
            'prefers_quiet_snapshot' => true,
            'roommate_rating_average_snapshot' => 4.8,
            'roommate_reviews_count_snapshot' => 3,
            'privacy_level' => 'summary',
            'can_show_before_booking' => true,
            'can_show_after_booking' => true,
        ];
    }
}
