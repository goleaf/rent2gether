<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomComfortDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomComfortDetail>
 */
class RoomComfortDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'has_window' => true,
            'windows_count' => 1,
            'view_from_window' => 'courtyard',
            'sun_side' => 'east',
            'has_lockable_door' => false,
            'has_room_key' => false,
            'has_wardrobe' => true,
            'has_shared_wardrobe' => true,
            'has_personal_lockers' => true,
            'has_desk' => true,
            'has_chairs' => true,
            'has_mirror' => true,
            'has_balcony' => false,
            'has_heating' => true,
            'heating_adjustable' => false,
            'has_air_conditioning' => false,
            'has_fan' => true,
            'has_humidifier' => false,
            'has_dehumidifier' => false,
            'winter_temperature_level' => 'warm',
            'summer_temperature_level' => 'normal',
            'ventilation_level' => 'good',
            'can_open_window' => true,
            'can_close_window' => true,
            'has_mosquito_net' => false,
            'has_draft' => false,
            'smell_level' => 'none',
            'has_damp_smell' => false,
            'has_tobacco_smell' => false,
            'has_pet_smell' => false,
            'light_level' => 'moderate',
            'has_main_light' => true,
            'has_night_light' => false,
            'has_personal_lamps' => true,
            'has_curtains' => true,
            'has_blackout_curtains' => false,
            'can_turn_light_at_night' => false,
            'can_work_at_night' => false,
            'can_eat_in_room' => false,
            'can_store_food_in_room' => false,
            'can_use_personal_lamp_at_night' => true,
            'noise_level' => 'moderate',
            'street_noise_level' => 'moderate',
            'neighbor_noise_level' => 'low',
            'corridor_noise_level' => 'moderate',
            'kitchen_noise_level' => 'moderate',
            'bathroom_noise_level' => 'low',
            'soundproofing_level' => 'normal',
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
        ];
    }
}
