<?php

namespace Database\Factories;

use App\Models\Country;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->country();
        $iso2 = $this->uniqueCode('iso2', 2);
        $iso3 = $this->uniqueCode('iso3', 3);

        return [
            'geoname_id' => $this->faker->unique()->numberBetween(100_000, 9_999_999),
            'iso2' => $iso2,
            'code' => $iso2,
            'iso3' => $iso3,
            'name_en' => $name,
            'name_ru' => null,
            'name_native' => null,
            'name' => $name,
            'name_normalized' => GeoNameNormalizer::normalize($name),
            'currency_code' => 'EUR',
            'phone_code' => '+370',
            'timezone_default' => 'Europe/Vilnius',
            'status' => Country::STATUS_ACTIVE,
            'source' => 'iso_3166',
            'is_active' => true,
        ];
    }

    private function uniqueCode(string $column, int $length): string
    {
        do {
            $code = strtoupper($this->faker->unique()->lexify(str_repeat('?', $length)));
        } while ($this->countryCodeExists($column, $code));

        return $code;
    }

    private function countryCodeExists(string $column, string $code): bool
    {
        $query = Country::query()->where($column, $code);

        if ($column === 'iso2') {
            $query->orWhere('code', $code);
        }

        return $query->exists();
    }
}
