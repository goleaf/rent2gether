<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Geo\CityAutocomplete;
use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CityAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_autocomplete_waits_for_two_characters(): void
    {
        $country = Country::factory()->create(['iso2' => 'LT', 'code' => 'LT', 'name_en' => 'Lithuania']);
        City::factory()->for($country)->create(['name' => 'Vilnius', 'ascii_name' => 'Vilnius']);

        Livewire::test(CityAutocomplete::class)
            ->set('query', 'v')
            ->assertDontSee('Vilnius');
    }

    public function test_city_autocomplete_prioritizes_prefix_matches_then_population(): void
    {
        $country = Country::factory()->create(['iso2' => 'AT', 'code' => 'AT', 'name_en' => 'Austria']);

        City::factory()->for($country)->create([
            'name' => 'Vilnius',
            'ascii_name' => 'Vilnius',
            'population' => 542366,
        ]);
        City::factory()->for($country)->create([
            'name' => 'Vienna',
            'ascii_name' => 'Vienna',
            'population' => 1973403,
        ]);
        City::factory()->for($country)->create([
            'name' => 'Davi City',
            'ascii_name' => 'Davi City',
            'population' => 5000000,
        ]);

        Livewire::test(CityAutocomplete::class)
            ->set('query', 'vi')
            ->assertSeeInOrder(['Vienna', 'Vilnius', 'Davi City']);
    }

    public function test_city_autocomplete_limits_results_to_ten_cities(): void
    {
        $country = Country::factory()->create(['iso2' => 'DE', 'code' => 'DE', 'name_en' => 'Germany']);

        foreach (range(1, 11) as $number) {
            City::factory()->for($country)->create([
                'name' => 'Vi City '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'ascii_name' => 'Vi City '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'population' => 1000 + $number,
            ]);
        }

        Livewire::test(CityAutocomplete::class)
            ->set('query', 'vi')
            ->assertSee('Vi City 11')
            ->assertDontSee('Vi City 01');
    }

    public function test_city_autocomplete_shows_no_results_empty_state(): void
    {
        Livewire::test(CityAutocomplete::class)
            ->set('query', 'zz')
            ->assertSee(__('search.city_autocomplete.no_results'))
            ->assertSee(__('search.city_autocomplete.no_results_text'));
    }
}
