<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->country();

        return [
            'code' => strtoupper($this->faker->unique()->lexify('??')),
            'iso3' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $name,
            'name_normalized' => Str::lower($name),
            'currency_code' => 'EUR',
            'phone_code' => '+370',
            'source' => 'iso_3166',
            'is_active' => true,
        ];
    }
}
