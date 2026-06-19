<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->state();

        return [
            'country_id' => Country::factory(),
            'code' => strtoupper($this->faker->lexify('??')),
            'name' => $name,
            'name_normalized' => Str::lower($name),
            'source' => 'geonames',
            'source_id' => (string) $this->faker->unique()->numberBetween(1000, 999999),
        ];
    }
}
