<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Room;
use App\Models\RoomCurrentOccupancySnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomCurrentOccupancySnapshot>
 */
class RoomCurrentOccupancySnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'property_id' => Property::factory(),
            'host_user_id' => User::factory(),
            'current_occupants_count' => 0,
            'current_bookings_count' => 0,
            'occupied_sleeping_places_count' => 0,
            'free_sleeping_places_count' => 0,
            'students_count' => 0,
            'workers_count' => 0,
            'tourists_count' => 0,
            'long_term_residents_count' => 0,
            'short_term_guests_count' => 0,
            'early_wakeup_count' => 0,
            'late_sleep_count' => 0,
            'night_work_count' => 0,
            'smokers_count' => 0,
            'non_smokers_count' => 0,
            'quiet_preferring_count' => 0,
            'social_count' => 0,
            'checkout_today_count' => 0,
            'checkin_today_count' => 0,
            'checkout_this_week_count' => 0,
            'has_open_complaints' => false,
            'has_open_maintenance' => false,
            'has_noise_warning' => false,
            'has_cleanliness_warning' => false,
            'last_recalculated_at' => now(),
        ];
    }
}
