<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceRule>
 */
class SleepingPlaceRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'rule_id' => null,
            'rule_key' => fake()->unique()->slug(2),
            'sort_order' => fake()->numberBetween(1, 20),
            'status' => 'active',
        ];
    }
}
