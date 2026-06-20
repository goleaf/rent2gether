<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyConditionDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyConditionDetail>
 */
class PropertyConditionDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'repair_state' => $this->faker->randomElement(['normal', 'good', 'needs_update']),
            'cleanliness_level' => $this->faker->randomElement(['moderate', 'good', 'high']),
            'smell_level' => $this->faker->randomElement(['none', 'low', 'moderate']),
            'has_tobacco_smell' => $this->faker->boolean(10),
            'has_pet_smell' => $this->faker->boolean(10),
            'has_damp_smell' => $this->faker->boolean(8),
            'has_kitchen_smell' => $this->faker->boolean(20),
            'ventilation_level' => $this->faker->randomElement(['moderate', 'good', 'high']),
            'has_ventilation' => true,
            'has_kitchen_hood' => $this->faker->boolean(75),
            'humidity_level' => $this->faker->randomElement(['normal', 'dry', 'humid']),
            'winter_temperature_level' => $this->faker->randomElement(['cool', 'normal', 'warm']),
            'summer_temperature_level' => $this->faker->randomElement(['normal', 'warm', 'hot']),
            'has_heating' => true,
            'heating_adjustable' => $this->faker->boolean(50),
            'has_air_conditioning' => $this->faker->boolean(20),
            'has_fan' => $this->faker->boolean(50),
            'has_hot_water' => true,
            'has_heating_problems' => $this->faker->boolean(5),
            'has_hot_water_problems' => $this->faker->boolean(5),
            'indoor_noise_level' => $this->faker->randomElement(['low', 'moderate', 'high']),
            'street_noise_level' => $this->faker->randomElement(['low', 'moderate', 'high']),
            'neighbor_noise_level' => $this->faker->randomElement(['low', 'moderate']),
            'soundproofing_level' => $this->faker->randomElement(['moderate', 'good']),
            'light_level' => $this->faker->randomElement(['moderate', 'good', 'bright']),
            'windows_face_yard' => $this->faker->boolean(50),
            'windows_face_street' => $this->faker->boolean(50),
            'has_blackout_curtains' => $this->faker->boolean(45),
            'has_insects' => $this->faker->boolean(3),
            'insects_note' => null,
            'has_mold' => $this->faker->boolean(3),
            'mold_note' => null,
            'has_damp_marks' => $this->faker->boolean(6),
            'regular_pest_control' => $this->faker->boolean(40),
            'furniture_condition' => $this->faker->randomElement(['normal', 'good']),
            'beds_condition' => $this->faker->randomElement(['normal', 'good']),
            'mattresses_condition' => $this->faker->randomElement(['normal', 'good']),
            'wardrobes_condition' => $this->faker->randomElement(['normal', 'good']),
            'tables_condition' => $this->faker->randomElement(['normal', 'good']),
            'chairs_condition' => $this->faker->randomElement(['normal', 'good']),
            'floor_condition' => $this->faker->randomElement(['normal', 'good']),
            'walls_condition' => $this->faker->randomElement(['normal', 'good']),
            'ceiling_condition' => $this->faker->randomElement(['normal', 'good']),
            'windows_condition' => $this->faker->randomElement(['normal', 'good']),
            'doors_condition' => $this->faker->randomElement(['normal', 'good']),
            'locks_condition' => $this->faker->randomElement(['normal', 'good']),
            'plumbing_condition' => $this->faker->randomElement(['normal', 'good']),
            'electricity_condition' => $this->faker->randomElement(['normal', 'good']),
            'kitchen_condition' => $this->faker->randomElement(['normal', 'good']),
            'bathroom_condition' => $this->faker->randomElement(['normal', 'good']),
            'toilet_condition' => $this->faker->randomElement(['normal', 'good']),
            'shower_condition' => $this->faker->randomElement(['normal', 'good']),
            'fridge_condition' => $this->faker->randomElement(['normal', 'good']),
            'stove_condition' => $this->faker->randomElement(['normal', 'good']),
            'washing_machine_condition' => $this->faker->randomElement(['normal', 'good']),
            'last_cleaned_at' => now()->subDays(2),
            'last_repaired_at' => now()->subMonths(6),
            'last_checked_at' => now()->subWeek(),
            'last_safety_checked_at' => now()->subMonth(),
            'last_plumbing_checked_at' => now()->subMonth(),
            'last_electricity_checked_at' => now()->subMonth(),
            'last_internet_checked_at' => now()->subMonth(),
            'owner_check_note' => $this->faker->optional()->sentence(),
        ];
    }
}
