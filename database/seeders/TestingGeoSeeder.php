<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Seeder;

class TestingGeoSeeder extends Seeder
{
    public function run(): void
    {
        $lithuania = Country::query()->updateOrCreate(
            ['iso2' => 'LT'],
            [
                'code' => 'LT',
                'iso3' => 'LTU',
                'name_en' => 'Lithuania',
                'name_ru' => 'Литва',
                'name_native' => 'Lietuva',
                'currency_code' => 'EUR',
                'phone_code' => '+370',
                'timezone_default' => 'Europe/Vilnius',
                'status' => Country::STATUS_ACTIVE,
                'source' => 'testing',
                'is_active' => true,
            ],
        );

        $germany = Country::query()->updateOrCreate(
            ['iso2' => 'DE'],
            [
                'code' => 'DE',
                'iso3' => 'DEU',
                'name_en' => 'Germany',
                'name_ru' => 'Германия',
                'name_native' => 'Deutschland',
                'currency_code' => 'EUR',
                'phone_code' => '+49',
                'timezone_default' => 'Europe/Berlin',
                'status' => Country::STATUS_ACTIVE,
                'source' => 'testing',
                'is_active' => true,
            ],
        );

        $vilniusRegion = Region::query()->updateOrCreate(
            ['country_id' => $lithuania->id, 'code' => 'VL'],
            [
                'name' => 'Vilnius County',
                'source' => 'testing',
                'source_id' => '864477',
            ],
        );

        $berlinRegion = Region::query()->updateOrCreate(
            ['country_id' => $germany->id, 'code' => 'BE'],
            [
                'name' => 'Berlin',
                'source' => 'testing',
                'source_id' => '2950157',
            ],
        );

        City::query()->updateOrCreate(
            ['geoname_id' => 593116],
            [
                'country_id' => $lithuania->id,
                'region_id' => $vilniusRegion->id,
                'name' => 'Vilnius',
                'ascii_name' => 'Vilnius',
                'alternate_names' => 'Vilna,Wilno,Вильнюс',
                'latitude' => 54.68916,
                'longitude' => 25.2798,
                'population' => 542366,
                'timezone' => 'Europe/Vilnius',
                'feature_class' => 'P',
                'feature_code' => 'PPLC',
                'status' => City::STATUS_ACTIVE,
                'source' => 'testing',
                'source_id' => '593116',
                'is_active' => true,
            ],
        );

        City::query()->updateOrCreate(
            ['geoname_id' => 2950159],
            [
                'country_id' => $germany->id,
                'region_id' => $berlinRegion->id,
                'name' => 'Berlin',
                'ascii_name' => 'Berlin',
                'alternate_names' => 'Berlino,Берлин',
                'latitude' => 52.52437,
                'longitude' => 13.41053,
                'population' => 3426354,
                'timezone' => 'Europe/Berlin',
                'feature_class' => 'P',
                'feature_code' => 'PPLC',
                'status' => City::STATUS_ACTIVE,
                'source' => 'testing',
                'source_id' => '2950159',
                'is_active' => true,
            ],
        );
    }
}
