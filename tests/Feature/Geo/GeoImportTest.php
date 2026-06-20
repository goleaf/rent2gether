<?php

namespace Tests\Feature\Geo;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Support\Geo\GeoNameNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GeoImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_import_creates_iso_country_records(): void
    {
        $source = storage_path('framework/testing/geo/countries.csv');

        File::ensureDirectoryExists(dirname($source));
        File::put($source, implode("\n", [
            'iso2,iso3,name_en,name_ru,name_native,phone_code,currency_code,timezone_default,status',
            'LT,LTU,Lithuania,Литва,Lietuva,+370,EUR,Europe/Vilnius,active',
        ]));

        $this->artisan('geo:import-countries', ['--source' => $source])
            ->assertSuccessful();

        $country = Country::query()->firstWhere('iso2', 'LT');

        $this->assertNotNull($country);
        $this->assertSame('LT', $country->code);
        $this->assertSame('LTU', $country->iso3);
        $this->assertSame('Lithuania', $country->name_en);
        $this->assertSame('lithuania', $country->name_normalized);
        $this->assertSame(Country::STATUS_ACTIVE, $country->status);
    }

    public function test_geonames_city_import_creates_city_and_region_records(): void
    {
        Country::factory()->create([
            'iso2' => 'LT',
            'code' => 'LT',
            'iso3' => 'LTU',
            'name_en' => 'Lithuania',
        ]);

        $source = storage_path('framework/testing/geo/cities1000.txt');

        File::ensureDirectoryExists(dirname($source));
        File::put($source, $this->geonamesRow([
            'geoname_id' => '593116',
            'name' => 'Vilnius',
            'ascii_name' => 'Vilnius',
            'alternate_names' => 'Vilna,Wilno',
            'latitude' => '54.68916',
            'longitude' => '25.27980',
            'feature_class' => 'P',
            'feature_code' => 'PPLC',
            'country_code' => 'LT',
            'admin1_code' => 'VL',
            'population' => '542366',
            'timezone' => 'Europe/Vilnius',
        ]));

        $this->artisan('geo:import-geonames-cities', ['--source' => $source])
            ->assertSuccessful();

        $city = City::query()->firstWhere('geoname_id', 593116);
        $region = Region::query()->firstWhere('code', 'VL');

        $this->assertNotNull($city);
        $this->assertNotNull($region);
        $this->assertTrue($city->region->is($region));
        $this->assertSame('Vilnius', $city->ascii_name);
        $this->assertSame('vilnius', $city->name_normalized);
        $this->assertSame(542366, $city->population);
        $this->assertSame(City::STATUS_ACTIVE, $city->status);
    }

    public function test_geo_search_normalization_and_rebuild_command(): void
    {
        $this->assertSame('sao paulo', GeoNameNormalizer::normalize(' São   Paulo '));
        $this->assertSame('munchen', GeoNameNormalizer::normalize('München'));

        $city = City::factory()->create([
            'name' => 'São Paulo',
            'ascii_name' => 'Sao Paulo',
        ]);

        City::query()->whereKey($city)->update(['name_normalized' => 'stale']);

        $this->artisan('geo:rebuild-search-index')
            ->assertSuccessful();

        $this->assertSame('sao paulo', $city->fresh()->name_normalized);
    }

    /**
     * @param  array<string, string>  $overrides
     */
    private function geonamesRow(array $overrides): string
    {
        $fields = [
            $overrides['geoname_id'],
            $overrides['name'],
            $overrides['ascii_name'],
            $overrides['alternate_names'],
            $overrides['latitude'],
            $overrides['longitude'],
            $overrides['feature_class'],
            $overrides['feature_code'],
            $overrides['country_code'],
            '',
            $overrides['admin1_code'],
            '',
            '',
            '',
            $overrides['population'],
            '',
            '',
            $overrides['timezone'],
            '2026-06-19',
        ];

        return implode("\t", $fields)."\n";
    }
}
