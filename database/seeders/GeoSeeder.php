<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityTranslation;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Region;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Database\Seeder;

class GeoSeeder extends Seeder
{
    public function run(): void
    {
        $countries = collect($this->countries())
            ->mapWithKeys(function (array $country): array {
                $model = Country::query()->updateOrCreate(
                    ['iso2' => $country['iso2']],
                    [
                        'code' => $country['iso2'],
                        'iso3' => $country['iso3'],
                        'name' => $country['name_en'],
                        'name_en' => $country['name_en'],
                        'name_ru' => $country['name_ru'],
                        'name_native' => $country['name_native'],
                        'name_normalized' => GeoNameNormalizer::normalize($country['name_en']),
                        'phone_code' => $country['phone_code'],
                        'currency_code' => $country['currency_code'],
                        'timezone_default' => $country['timezone_default'],
                        'status' => Country::STATUS_ACTIVE,
                        'source' => 'iso_3166_demo',
                        'is_active' => true,
                    ],
                );

                $this->countryTranslation($model, 'en', $country['name_en']);
                $this->countryTranslation($model, 'ru', $country['name_ru']);

                return [$country['iso2'] => $model];
            });

        $regions = collect($this->regions())
            ->mapWithKeys(function (array $region) use ($countries): array {
                $country = $countries->get($region['country']);

                return [
                    $region['key'] => Region::query()->updateOrCreate(
                        ['country_id' => $country->id, 'code' => $region['code']],
                        [
                            'name' => $region['name'],
                            'name_normalized' => GeoNameNormalizer::normalize($region['name']),
                            'source' => 'geonames_demo',
                            'source_id' => $region['source_id'],
                        ],
                    ),
                ];
            });

        foreach ($this->cities() as $city) {
            $country = $countries->get($city['country']);
            $region = $regions->get($city['region']);

            $model = City::query()->updateOrCreate(
                ['geoname_id' => $city['geoname_id']],
                [
                    'country_id' => $country->id,
                    'region_id' => $region?->id,
                    'name' => $city['name'],
                    'ascii_name' => $city['ascii_name'],
                    'alternate_names' => $city['alternate_names'],
                    'name_normalized' => GeoNameNormalizer::normalize($city['name']),
                    'latitude' => $city['latitude'],
                    'longitude' => $city['longitude'],
                    'population' => $city['population'],
                    'timezone' => $city['timezone'],
                    'feature_class' => 'P',
                    'feature_code' => $city['feature_code'],
                    'status' => City::STATUS_ACTIVE,
                    'source' => 'geonames_demo',
                    'source_id' => (string) $city['geoname_id'],
                    'is_active' => true,
                ],
            );

            foreach ($city['translations'] as $locale => $name) {
                $this->cityTranslation($model, $locale, $name);
            }
        }
    }

    private function countryTranslation(Country $country, string $locale, string $name): void
    {
        CountryTranslation::query()->updateOrCreate(
            [
                'country_id' => $country->id,
                'locale' => CountryTranslation::normalizeLocale($locale),
                'name_normalized' => GeoNameNormalizer::normalize($name),
            ],
            [
                'name' => $name,
                'source' => 'geo_demo',
                'source_id' => $country->geoname_id ? (string) $country->geoname_id : $country->iso2,
                'is_preferred' => true,
            ],
        );
    }

    private function cityTranslation(City $city, string $locale, string $name): void
    {
        CityTranslation::query()->updateOrCreate(
            [
                'city_id' => $city->id,
                'locale' => CountryTranslation::normalizeLocale($locale),
                'name_normalized' => GeoNameNormalizer::normalize($name),
            ],
            [
                'name' => $name,
                'source' => 'geo_demo',
                'source_id' => $city->geoname_id ? (string) $city->geoname_id : null,
                'is_preferred' => true,
            ],
        );
    }

    /**
     * @return list<array{iso2:string,iso3:string,name_en:string,name_ru:string,name_native:string,phone_code:string,currency_code:string,timezone_default:string}>
     */
    private function countries(): array
    {
        return [
            [
                'iso2' => 'LT',
                'iso3' => 'LTU',
                'name_en' => 'Lithuania',
                'name_ru' => 'Литва',
                'name_native' => 'Lietuva',
                'phone_code' => '+370',
                'currency_code' => 'EUR',
                'timezone_default' => 'Europe/Vilnius',
            ],
            [
                'iso2' => 'DE',
                'iso3' => 'DEU',
                'name_en' => 'Germany',
                'name_ru' => 'Германия',
                'name_native' => 'Deutschland',
                'phone_code' => '+49',
                'currency_code' => 'EUR',
                'timezone_default' => 'Europe/Berlin',
            ],
        ];
    }

    /**
     * @return list<array{key:string,country:string,code:string,name:string,source_id:string}>
     */
    private function regions(): array
    {
        return [
            ['key' => 'lt-vl', 'country' => 'LT', 'code' => 'VL', 'name' => 'Vilnius County', 'source_id' => '864477'],
            ['key' => 'lt-ku', 'country' => 'LT', 'code' => 'KU', 'name' => 'Kaunas County', 'source_id' => '864479'],
            ['key' => 'lt-kl', 'country' => 'LT', 'code' => 'KL', 'name' => 'Klaipeda County', 'source_id' => '864478'],
            ['key' => 'de-be', 'country' => 'DE', 'code' => 'BE', 'name' => 'Berlin', 'source_id' => '2950157'],
            ['key' => 'de-by', 'country' => 'DE', 'code' => 'BY', 'name' => 'Bavaria', 'source_id' => '2951839'],
        ];
    }

    /**
     * @return list<array{geoname_id:int,country:string,region:string,name:string,ascii_name:string,alternate_names:string,latitude:float,longitude:float,population:int,timezone:string,feature_code:string,translations:array<string,string>}>
     */
    private function cities(): array
    {
        return [
            [
                'geoname_id' => 593116,
                'country' => 'LT',
                'region' => 'lt-vl',
                'name' => 'Vilnius',
                'ascii_name' => 'Vilnius',
                'alternate_names' => 'Vilna,Wilno,Вильнюс',
                'latitude' => 54.68916,
                'longitude' => 25.2798,
                'population' => 542366,
                'timezone' => 'Europe/Vilnius',
                'feature_code' => 'PPLC',
                'translations' => ['en' => 'Vilnius', 'ru' => 'Вильнюс'],
            ],
            [
                'geoname_id' => 598316,
                'country' => 'LT',
                'region' => 'lt-ku',
                'name' => 'Kaunas',
                'ascii_name' => 'Kaunas',
                'alternate_names' => 'Kovno,Каунас',
                'latitude' => 54.90272,
                'longitude' => 23.90961,
                'population' => 289380,
                'timezone' => 'Europe/Vilnius',
                'feature_code' => 'PPLA',
                'translations' => ['en' => 'Kaunas', 'ru' => 'Каунас'],
            ],
            [
                'geoname_id' => 598098,
                'country' => 'LT',
                'region' => 'lt-kl',
                'name' => 'Klaipeda',
                'ascii_name' => 'Klaipeda',
                'alternate_names' => 'Memel,Клайпеда',
                'latitude' => 55.71722,
                'longitude' => 21.1175,
                'population' => 183433,
                'timezone' => 'Europe/Vilnius',
                'feature_code' => 'PPLA',
                'translations' => ['en' => 'Klaipeda', 'ru' => 'Клайпеда'],
            ],
            [
                'geoname_id' => 2950159,
                'country' => 'DE',
                'region' => 'de-be',
                'name' => 'Berlin',
                'ascii_name' => 'Berlin',
                'alternate_names' => 'Berlino,Берлин',
                'latitude' => 52.52437,
                'longitude' => 13.41053,
                'population' => 3426354,
                'timezone' => 'Europe/Berlin',
                'feature_code' => 'PPLC',
                'translations' => ['en' => 'Berlin', 'ru' => 'Берлин'],
            ],
            [
                'geoname_id' => 2867714,
                'country' => 'DE',
                'region' => 'de-by',
                'name' => 'Munich',
                'ascii_name' => 'Munich',
                'alternate_names' => 'Muenchen,München,Мюнхен',
                'latitude' => 48.13743,
                'longitude' => 11.57549,
                'population' => 1260391,
                'timezone' => 'Europe/Berlin',
                'feature_code' => 'PPLA',
                'translations' => ['en' => 'Munich', 'ru' => 'Мюнхен'],
            ],
        ];
    }
}
