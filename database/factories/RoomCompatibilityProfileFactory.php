<?php

namespace Database\Factories;

use App\Enums\GenderType;
use App\Models\Room;
use App\Models\RoomCompatibilityProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomCompatibilityProfile>
 */
class RoomCompatibilityProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'gender_policy' => GenderType::Mixed->value,
            'is_private' => false,
            'is_shared' => true,
            'max_people_in_room' => 4,
            'current_people_count' => 1,
            'typical_people_count' => 3,
            'noise_level' => 'moderate',
            'light_level' => 'moderate',
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
            'can_turn_light_at_night' => false,
            'can_work_at_night' => false,
            'can_eat' => false,
            'can_store_food' => true,
            'has_workspace' => false,
            'has_desk' => false,
            'has_chair' => true,
            'has_personal_lockers' => true,
            'has_lock' => true,
            'has_window' => true,
            'has_air_conditioning' => false,
            'has_heating' => true,
            'can_open_window' => true,
            'smoking_allowed' => false,
            'pets_present' => false,
            'pets_allowed' => false,
            'kitchen_night_use_allowed' => false,
            'washing_machine_available' => true,
            'long_stay_allowed' => true,
            'short_stay_allowed' => true,
            'late_entry_allowed' => false,
        ];
    }
}
