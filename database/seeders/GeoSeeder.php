<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Seeder;

class GeoSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::query()->firstOrCreate(
            ['code' => 'LT'],
            [
                'iso3' => 'LTU',
                'name' => 'Lithuania',
                'name_normalized' => 'lithuania',
                'currency_code' => 'EUR',
                'phone_code' => '+370',
                'source' => 'iso_3166',
                'is_active' => true,
            ]
        );

        $region = Region::query()->firstOrCreate(
            ['country_id' => $country->id, 'code' => 'VL'],
            [
                'name' => 'Vilnius County',
                'name_normalized' => 'vilnius county',
                'source' => 'geonames',
                'source_id' => '864477',
            ]
        );

        City::query()->firstOrCreate(
            ['source' => 'geonames', 'source_id' => '593116'],
            [
                'country_id' => $country->id,
                'region_id' => $region->id,
                'name' => 'Vilnius',
                'name_normalized' => 'vilnius',
                'latitude' => 54.68916,
                'longitude' => 25.2798,
                'population' => 542366,
                'timezone' => 'Europe/Vilnius',
                'is_active' => true,
            ]
        );
    }
}
