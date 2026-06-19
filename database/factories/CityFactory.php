<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->city();

        return [
            'country_id' => Country::factory(),
            'region_id' => Region::factory(),
            'name' => $name,
            'name_normalized' => Str::lower($name),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'population' => $this->faker->numberBetween(5_000, 800_000),
            'timezone' => 'Europe/Vilnius',
            'source' => 'geonames',
            'source_id' => (string) $this->faker->unique()->numberBetween(1000, 999999),
            'is_active' => true,
        ];
    }
}
