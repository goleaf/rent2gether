<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlacePhysicalDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlacePhysicalDetail>
 */
class SleepingPlacePhysicalDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'length_cm' => 200,
            'width_cm' => 90,
            'height_cm' => null,
            'height_from_floor_cm' => null,
            'clearance_above_cm' => null,
            'ladder_available' => false,
            'ladder_comfort_level' => null,
            'safety_rail_available' => false,
            'safety_rail_height_cm' => null,
            'max_weight_kg' => 120,
            'suitable_for_tall_person' => true,
            'suitable_for_heavy_person' => true,
            'suitable_for_elderly' => false,
            'suitable_for_limited_mobility' => false,
            'not_suitable_for_limited_mobility' => true,
            'frame_material' => 'wood',
            'frame_stability_level' => 'good',
            'squeak_level' => 'low',
        ];
    }
}
