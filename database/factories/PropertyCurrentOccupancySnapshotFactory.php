<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyCurrentOccupancySnapshot>
 */
class PropertyCurrentOccupancySnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'host_user_id' => User::factory(),
            'current_occupants_count' => 0,
            'current_bookings_count' => 0,
            'occupied_rooms_count' => 0,
            'occupied_sleeping_places_count' => 0,
            'free_sleeping_places_count' => 0,
            'checkout_today_count' => 0,
            'checkin_today_count' => 0,
            'checkout_this_week_count' => 0,
            'has_open_complaints' => false,
            'has_open_maintenance' => false,
            'has_cleaning_needed' => false,
            'has_inspection_needed' => false,
            'last_recalculated_at' => now(),
        ];
    }
}
