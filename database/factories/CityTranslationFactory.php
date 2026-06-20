<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\CityTranslation;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CityTranslation>
 */
class CityTranslationFactory extends Factory
{
    protected $model = CityTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->city();

        return [
            'city_id' => City::factory(),
            'locale' => 'en',
            'name' => $name,
            'name_normalized' => GeoNameNormalizer::normalize($name),
            'source' => 'factory',
            'source_id' => null,
            'is_preferred' => false,
            'is_short' => false,
            'is_colloquial' => false,
            'is_historic' => false,
            'valid_from' => null,
            'valid_to' => null,
        ];
    }
}
