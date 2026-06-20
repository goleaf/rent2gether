<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->city();
        $geonameId = $this->faker->unique()->numberBetween(1000, 999999);

        return [
            'geoname_id' => $geonameId,
            'country_id' => Country::factory(),
            'region_id' => Region::factory(),
            'name' => $name,
            'ascii_name' => $name,
            'alternate_names' => null,
            'name_normalized' => GeoNameNormalizer::normalize($name),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'population' => $this->faker->numberBetween(5_000, 800_000),
            'timezone' => 'Europe/Vilnius',
            'feature_class' => 'P',
            'feature_code' => 'PPL',
            'status' => City::STATUS_ACTIVE,
            'source' => 'geonames',
            'source_id' => (string) $geonameId,
            'is_active' => true,
        ];
    }
}
