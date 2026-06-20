<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropertyRule> */
class PropertyRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'rule_key' => $this->faker->randomElement(['smoking', 'quiet_hours', 'pets']),
            'allowed' => false,
            'starts_at_time' => null,
            'ends_at_time' => null,
            'description' => null,
            'strict' => false,
            'visible_to_guest' => true,
            'sort_order' => 0,
            'status' => 'active',
        ];
    }
}
