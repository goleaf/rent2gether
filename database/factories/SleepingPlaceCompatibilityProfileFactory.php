<?php

namespace Database\Factories;

use App\Enums\SleepingPlaceType;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCompatibilityProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceCompatibilityProfile>
 */
class SleepingPlaceCompatibilityProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'sleeping_place_type' => SleepingPlaceType::Single->value,
            'is_top_bunk' => false,
            'is_bottom_bunk' => false,
            'is_sofa' => false,
            'is_floor_mattress' => false,
            'is_for_one_person' => true,
            'is_for_couple' => false,
            'has_curtain' => false,
            'has_locker' => true,
            'locker_has_lock' => true,
            'has_power_socket' => true,
            'has_usb_charger' => false,
            'has_personal_lamp' => true,
            'has_shelf' => true,
            'has_luggage_space' => true,
            'has_bedding' => true,
            'has_towel' => false,
            'privacy_level' => 'moderate',
            'noise_level_near_place' => 'moderate',
            'light_level_near_place' => 'moderate',
            'suitable_for_tall_person' => true,
            'suitable_for_heavy_person' => null,
            'suitable_for_limited_mobility' => false,
            'min_nights' => 1,
            'max_nights' => null,
            'can_extend' => true,
            'instant_booking_enabled' => false,
        ];
    }
}
