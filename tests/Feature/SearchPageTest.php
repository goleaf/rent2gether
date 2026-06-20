<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Search\SleepingPlaceSearch;
use App\Models\Amenity;
use App\Models\AvailabilityDay;
use App\Models\Bed;
use App\Models\City;
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-19 12:00:00');
        CarbonImmutable::setTestNow('2026-06-19 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_search_page_loads(): void
    {
        $response = $this->get(route('search.index', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSeeLivewire(SleepingPlaceSearch::class);
    }

    public function test_search_page_shows_active_sleeping_places(): void
    {
        $city = $this->city('Berlin');
        $this->createSearchPlace('Test Sleeping Place Alpha', $city);

        $response = $this->get(route('search.index', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSee('Test Sleeping Place Alpha');
    }

    public function test_search_filters_by_city(): void
    {
        $berlin = $this->city('Berlin');
        $paris = $this->city('Paris');
        $this->createSearchPlace('Berlin Sleeping Place', $berlin);
        $this->createSearchPlace('Paris Sleeping Place', $paris);

        $response = $this->get(route('search.index', ['locale' => 'en', 'city' => $berlin->id]));

        $response->assertOk();
        $response->assertSee('Berlin Sleeping Place');
        $response->assertDontSee('Paris Sleeping Place');
    }

    public function test_search_filters_by_date_availability(): void
    {
        $city = $this->city('Vilnius');
        $open = $this->createSearchPlace('Open Sleeping Place', $city);
        $blocked = $this->createSearchPlace('Blocked Sleeping Place', $city);
        AvailabilityDay::factory()->for($blocked)->create([
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'in' => '2026-07-10',
            'out' => '2026-07-12',
        ]));

        $response->assertOk();
        $response->assertSee('Open Sleeping Place');
        $response->assertDontSee('Blocked Sleeping Place');
        $this->assertTrue($open->exists);
    }

    public function test_search_filters_by_price(): void
    {
        $city = $this->city('Riga');
        $this->createSearchPlace('Budget Place', $city, ['base_price_per_night' => 25]);
        $this->createSearchPlace('Premium Place', $city, ['base_price_per_night' => 80]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'price_max' => 30,
        ]));

        $response->assertOk();
        $response->assertSee('Budget Place');
        $response->assertDontSee('Premium Place');
    }

    public function test_search_filters_by_amenity(): void
    {
        $city = $this->city('Tallinn');
        $wifi = $this->amenity('wifi', 'Wi-Fi');
        $withWifi = $this->createSearchPlace('Wi-Fi Place', $city);
        $withoutWifi = $this->createSearchPlace('Simple Place', $city);
        $withWifi->property->amenities()->attach($wifi);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'wifi' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Wi-Fi Place');
        $response->assertDontSee('Simple Place');
        $this->assertTrue($withoutWifi->exists);
    }

    public function test_search_filters_by_room_gender_policy(): void
    {
        $city = $this->city('Kaunas');
        $this->createSearchPlace('Female Room Place', $city, [], ['type' => PropertyType::Apartment], [
            'gender_policy' => GenderType::Female,
        ]);
        $this->createSearchPlace('Mixed Room Place', $city, [], ['type' => PropertyType::Apartment], [
            'gender_policy' => GenderType::Mixed,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'gender' => GenderType::Female->value,
        ]));

        $response->assertOk();
        $response->assertSee('Female Room Place');
        $response->assertDontSee('Mixed Room Place');
    }

    public function test_search_url_state_initializes_filters(): void
    {
        $city = $this->city('Warsaw');
        $this->createSearchPlace('Warsaw Place', $city, ['base_price_per_night' => 20]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'price_max' => 30,
            'guests' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Warsaw');
        $response->assertSee('Warsaw Place');

        Livewire::test(SleepingPlaceSearch::class)
            ->set('cityQuery', 'War')
            ->set('priceMax', '30')
            ->set('wifi', true)
            ->assertSet('city', 'War')
            ->assertSet('priceMax', '30')
            ->assertSet('wifi', true);
    }

    public function test_search_results_are_localized(): void
    {
        $city = $this->city('Minsk');
        $this->createSearchPlace('English sleeping place', $city, [], [], [], 'Русское спальное место');

        $response = $this->get(route('search.index', ['locale' => 'ru', 'city' => $city->id]));

        $response->assertOk();
        $response->assertSee('Русское спальное место');
        $response->assertDontSee('English sleeping place');
    }

    public function test_search_empty_state_is_friendly(): void
    {
        $city = $this->city('Empty City');

        $response = $this->get(route('search.index', ['locale' => 'en', 'city' => $city->id]));

        $response->assertOk();
        $response->assertSee(__('search.empty.title'));
        $response->assertSee(__('search.empty.change_dates'));
        $response->assertSee(__('search.empty.increase_budget'));
        $response->assertSee(__('search.empty.nearby_cities'));
        $response->assertSee(__('search.empty.fewer_filters'));
    }

    public function test_bed_detail_page_loads(): void
    {
        $host = User::factory()->create();
        $property = Property::factory()->for($host, 'host')->create(['status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => 'active']);
        $bed = Bed::factory()->for($room)->create(['status' => 'active', 'title' => 'Cozy Single']);

        $response = $this->get(route('beds.show', ['locale' => 'en', 'bed' => $bed]));

        $response->assertStatus(200);
        $response->assertSee('Cozy Single');
    }

    public function test_bed_detail_shows_host_name(): void
    {
        $host = User::factory()->create(['name' => 'Jane Host']);
        $property = Property::factory()->for($host, 'host')->create(['status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => 'active']);
        $bed = Bed::factory()->for($room)->create(['status' => 'active']);

        $response = $this->get(route('beds.show', ['locale' => 'en', 'bed' => $bed]));

        $response->assertStatus(200);
        $response->assertSee('Jane Host');
    }

    public function test_bed_detail_shows_guest_compatibility_block(): void
    {
        $guest = User::factory()->create();
        $guest->guestPreference()->create([
            'preferred_currency' => 'EUR',
            'preferred_budget_max' => 40,
            'wants_wifi' => true,
            'needs_quiet_hours' => true,
        ]);

        $host = User::factory()->create(['name' => 'Jane Host']);
        $property = Property::factory()->for($host, 'host')->create([
            'status' => 'active',
            'amenities' => ['wifi'],
            'rules' => ['quiet_hours', 'no_smoking'],
        ]);
        $room = Room::factory()->for($property)->create(['status' => 'active']);
        $bed = Bed::factory()->for($room)->create([
            'status' => 'active',
            'price_per_night' => 25,
        ]);

        $response = $this->actingAs($guest)->get(route('beds.show', ['locale' => 'en', 'bed' => $bed]));

        $response->assertOk();
        $response->assertSee('Why this place fits');
        $response->assertSee('Pay attention');
        $response->assertSee('Wi-Fi is available.');
    }

    private function city(string $name): City
    {
        $country = Country::query()->firstOrCreate(
            ['iso2' => 'DE'],
            [
                'code' => 'DE',
                'iso3' => 'DEU',
                'name' => 'Germany',
                'name_en' => 'Germany',
                'status' => Country::STATUS_ACTIVE,
                'is_active' => true,
            ],
        );

        return City::factory()->for($country)->create([
            'name' => $name,
            'ascii_name' => $name,
            'status' => City::STATUS_ACTIVE,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $placeAttributes
     * @param  array<string, mixed>  $propertyAttributes
     * @param  array<string, mixed>  $roomAttributes
     */
    private function createSearchPlace(
        string $title,
        City $city,
        array $placeAttributes = [],
        array $propertyAttributes = [],
        array $roomAttributes = [],
        ?string $ruTitle = null,
    ): SleepingPlace {
        $host = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'rating_average' => 4.8,
            'reviews_count' => 12,
            'verified_at' => now(),
        ]);
        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create(array_merge([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $city->country_id,
                'city_id' => $city->id,
                'city' => $city->name,
                'district' => 'Central',
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
            ], $propertyAttributes));
        $room = Room::factory()->for($property)->create(array_merge([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::NoRestriction,
            'max_guests' => 4,
            'available_places_count' => 1,
        ], $roomAttributes));
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create(array_merge([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::Single,
                'display_name' => $title,
                'base_price_per_night' => 30,
                'currency' => 'EUR',
                'max_guests' => 1,
                'min_nights' => 1,
                'max_nights' => null,
            ], $placeAttributes));

        $place->translations()->create(['locale' => 'en', 'title' => $title]);
        $place->translations()->create(['locale' => 'ru', 'title' => $ruTitle ?: $title]);

        return $place;
    }

    private function amenity(string $slug, string $label): Amenity
    {
        $amenity = Amenity::factory()->create([
            'slug' => $slug,
            'name_normalized' => $slug,
            'category' => 'property',
            'status' => 'active',
        ]);
        $amenity->translations()->create(['locale' => 'en', 'name' => $label, 'name_normalized' => str($label)->lower()->toString()]);
        $amenity->translations()->create(['locale' => 'ru', 'name' => $label, 'name_normalized' => str($label)->lower()->toString()]);

        return $amenity;
    }
}
