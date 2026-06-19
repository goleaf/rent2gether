<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyTranslation>
 */
class PropertyTranslationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'locale' => 'en',
            'title' => $this->faker->sentence(3),
            'summary' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'neighborhood_description' => $this->faker->sentence(),
            'getting_there' => $this->faker->sentence(),
            'what_guests_like' => $this->faker->sentence(),
            'what_to_know' => $this->faker->sentence(),
            'suitable_for' => $this->faker->sentence(),
            'not_suitable_for' => $this->faker->sentence(),
            'check_in_instructions' => $this->faker->sentence(),
            'check_out_instructions' => $this->faker->sentence(),
            'house_rules_text' => $this->faker->sentence(),
            'safety_notes' => $this->faker->sentence(),
        ];
    }
}
