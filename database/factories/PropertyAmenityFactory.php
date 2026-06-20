<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyAmenity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropertyAmenity> */
class PropertyAmenityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'amenity_key' => $this->faker->randomElement(['wifi', 'kitchen', 'shower']),
            'available' => true,
            'description' => null,
            'visible_to_guest' => true,
        ];
    }
}
