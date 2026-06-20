<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\CountryTranslation;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CountryTranslation>
 */
class CountryTranslationFactory extends Factory
{
    protected $model = CountryTranslation::class;

    public function definition(): array
    {
        $name = $this->faker->country();

        return [
            'country_id' => Country::factory(),
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
