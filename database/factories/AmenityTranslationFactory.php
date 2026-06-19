<?php

namespace Database\Factories;

use App\Models\Amenity;
use App\Models\AmenityTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AmenityTranslation>
 */
class AmenityTranslationFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'amenity_id' => Amenity::factory(),
            'locale' => 'en',
            'name' => $name,
            'name_normalized' => Str::lower($name),
            'description' => $this->faker->sentence(),
        ];
    }
}
