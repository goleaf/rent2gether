<?php

namespace Tests\Feature\Geo;

use App\Actions\Geo\SeedGeonamesDataAction;
use App\Livewire\Geo\CityAutocomplete;
use App\Models\City;
use App\Models\CityTranslation;
use App\Models\Country;
use App\Models\CountryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class GeoNamesSeedImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_geonames_seed_imports_countries_cities_and_locale_translations_from_local_files(): void
    {
        $storagePath = storage_path('framework/testing/geonames/full-seed');
        $this->writeGeonamesFixtures($storagePath);

        config([
            'geo.geonames.storage_path' => $storagePath,
            'geo.geonames.dataset' => 'allCountries',
            'geo.geonames.download_enabled' => false,
            'geo.geonames.import_alternate_names' => true,
            'geo.geonames.languages' => 'all',
            'geo.geonames.canonical_locale' => 'en',
            'geo.geonames.chunk_size' => 2,
        ]);

        $result = app(SeedGeonamesDataAction::class)->handle();

        $this->assertSame(2, $result['countries']);
        $this->assertSame(1, $result['cities']);

        $lithuania = Country::query()->where('iso2', 'LT')->firstOrFail();
        $vilnius = City::query()->where('geoname_id', 593116)->firstOrFail();

        $this->assertSame(597427, $lithuania->geoname_id);
        $this->assertSame('Lithuania', $lithuania->localizedName('en'));
        $this->assertSame('Литва', $lithuania->localizedName('ru'));
        $this->assertSame('Вильнюс', $vilnius->localizedName('ru'));
        $this->assertSame('Vilnius', $vilnius->localizedName('lt'));

        $this->assertDatabaseHas('country_translations', [
            'country_id' => $lithuania->id,
            'locale' => 'lt',
            'name' => 'Lietuva',
        ]);
        $this->assertDatabaseHas('city_translations', [
            'city_id' => $vilnius->id,
            'locale' => 'ru',
            'name' => 'Вильнюс',
        ]);
        $this->assertDatabaseMissing('city_translations', [
            'city_id' => $vilnius->id,
            'locale' => 'link',
        ]);
        $this->assertDatabaseMissing('cities', [
            'geoname_id' => 777777,
        ]);
    }

    public function test_city_autocomplete_uses_current_interface_locale_for_search_and_display(): void
    {
        app()->setLocale('ru');

        $country = Country::factory()->create([
            'iso2' => 'LT',
            'code' => 'LT',
            'name' => 'Lithuania',
        ]);
        CountryTranslation::factory()->for($country)->create([
            'locale' => 'ru',
            'name' => 'Литва',
        ]);
        CountryTranslation::factory()->for($country)->create([
            'locale' => 'en',
            'name' => 'Lithuania',
        ]);

        $city = City::factory()->for($country)->create([
            'name' => 'Vilnius',
            'ascii_name' => 'Vilnius',
            'population' => 542366,
        ]);
        CityTranslation::factory()->for($city)->create([
            'locale' => 'ru',
            'name' => 'Вильнюс',
        ]);
        CityTranslation::factory()->for($city)->create([
            'locale' => 'en',
            'name' => 'Vilnius',
        ]);

        Livewire::test(CityAutocomplete::class)
            ->set('query', 'Ви')
            ->assertSee('Вильнюс')
            ->assertSee('Литва')
            ->assertDontSee('Lithuania');
    }

    private function writeGeonamesFixtures(string $storagePath): void
    {
        File::deleteDirectory($storagePath);
        File::ensureDirectoryExists($storagePath);

        File::put($storagePath.'/countryInfo.txt', implode("\n", [
            '#ISO	ISO3	ISO-Numeric	fips	Country	Capital	Area(in sq km)	Population	Continent	tld	CurrencyCode	CurrencyName	Phone	Postal Code Format	Postal Code Regex	Languages	geonameid	neighbours	EquivalentFipsCode',
            $this->countryInfoRow('LT', 'LTU', '440', 'LH', 'Lithuania', 'Vilnius', 'EUR', '370', 'lt,ru,en', '597427'),
            $this->countryInfoRow('DE', 'DEU', '276', 'GM', 'Germany', 'Berlin', 'EUR', '49', 'de,en', '2921044'),
            '',
        ]));

        File::put($storagePath.'/allCountries.txt', implode('', [
            $this->geonamesRow([
                'geoname_id' => '593116',
                'name' => 'Vilnius',
                'ascii_name' => 'Vilnius',
                'alternate_names' => 'Vilna,Wilno,Вильнюс',
                'latitude' => '54.68916',
                'longitude' => '25.27980',
                'feature_class' => 'P',
                'feature_code' => 'PPLC',
                'country_code' => 'LT',
                'admin1_code' => 'VL',
                'population' => '542366',
                'timezone' => 'Europe/Vilnius',
            ]),
            $this->geonamesRow([
                'geoname_id' => '777777',
                'name' => 'Fixture Lake',
                'ascii_name' => 'Fixture Lake',
                'alternate_names' => '',
                'latitude' => '54.00000',
                'longitude' => '25.00000',
                'feature_class' => 'H',
                'feature_code' => 'LK',
                'country_code' => 'LT',
                'admin1_code' => 'VL',
                'population' => '0',
                'timezone' => 'Europe/Vilnius',
            ]),
        ]));

        File::put($storagePath.'/alternateNamesV2.txt', implode("\n", [
            $this->alternateNameRow('1', '597427', 'lt', 'Lietuva', '1'),
            $this->alternateNameRow('2', '597427', 'ru', 'Литва', '1'),
            $this->alternateNameRow('3', '593116', 'ru', 'Вильнюс', '1'),
            $this->alternateNameRow('4', '593116', 'lt', 'Vilnius', '1'),
            $this->alternateNameRow('5', '593116', 'link', 'https://example.test/vilnius', '0'),
            '',
        ]));
    }

    private function countryInfoRow(string $iso2, string $iso3, string $isoNumeric, string $fips, string $name, string $capital, string $currency, string $phone, string $languages, string $geonameId): string
    {
        return implode("\t", [
            $iso2,
            $iso3,
            $isoNumeric,
            $fips,
            $name,
            $capital,
            '65300',
            '2800000',
            'EU',
            '.'.strtolower($iso2),
            $currency,
            'Euro',
            $phone,
            '',
            '',
            $languages,
            $geonameId,
            '',
            '',
        ]);
    }

    /**
     * @param  array<string, string>  $overrides
     */
    private function geonamesRow(array $overrides): string
    {
        return implode("\t", [
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
            '2026-06-20',
        ])."\n";
    }

    private function alternateNameRow(string $id, string $geonameId, string $locale, string $name, string $preferred): string
    {
        return implode("\t", [
            $id,
            $geonameId,
            $locale,
            $name,
            $preferred,
            '0',
            '0',
            '0',
            '',
            '',
        ]);
    }
}
