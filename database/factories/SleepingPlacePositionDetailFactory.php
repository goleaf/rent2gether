<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlacePositionDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlacePositionDetail>
 */
class SleepingPlacePositionDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'privacy_level' => 'moderate',
            'has_curtain' => true,
            'curtain_full_cover' => true,
            'curtain_partial_cover' => false,
            'has_partition' => false,
            'has_side_wall' => true,
            'capsule_style' => false,
            'visible_from_door' => false,
            'visible_from_passage' => true,
            'visible_from_other_beds' => true,
            'can_block_light' => true,
            'has_personal_lamp' => true,
            'lamp_adjustable' => true,
            'has_power_socket' => true,
            'power_sockets_count' => 1,
            'socket_near_head' => true,
            'socket_near_feet' => false,
            'has_usb_charger' => false,
            'has_usb_c_charger' => false,
            'has_extension_cord' => false,
            'has_shelf' => true,
            'has_hook' => true,
            'has_phone_holder' => false,
            'has_small_table' => false,
            'near_door' => false,
            'near_window' => false,
            'near_radiator' => false,
            'near_air_conditioner' => false,
            'near_power_socket' => true,
            'near_passage' => false,
            'near_wardrobe' => false,
            'near_desk' => false,
            'near_balcony' => false,
            'near_bathroom' => false,
            'near_kitchen' => false,
            'in_room_corner' => true,
            'in_room_center' => false,
            'near_wall' => true,
            'between_two_beds' => false,
            'narrow_passage_nearby' => false,
            'noise_level_near_place' => 'moderate',
            'light_level_near_place' => 'moderate',
            'morning_light' => false,
            'corridor_light_reaches' => false,
            'draft_nearby' => false,
        ];
    }
}
