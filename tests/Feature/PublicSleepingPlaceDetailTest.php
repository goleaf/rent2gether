<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Places\ShowSleepingPlace;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicSleepingPlaceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-20 10:00:00');
        CarbonImmutable::setTestNow('2026-06-20 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_detail_renders_in_english_and_russian(): void
    {
        $place = $this->createPlace('Central lower bed', 'Нижнее место в центре');

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee('Central lower bed')
            ->assertSee(__('listing.detail.summary.title'));

        $this->get(route('places.show', ['locale' => 'ru', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee('Нижнее место в центре')
            ->assertSee('Кратко о месте');
    }

    public function test_selected_dates_update_price(): void
    {
        $place = $this->createPlace('Price test place');

        $component = Livewire::test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15');

        $quote = $component->get('quote');

        $this->assertSame(5, $quote['nights_count']);
        $this->assertSame(192.5, $quote['total_amount']);
    }

    public function test_unavailable_warning_is_shown(): void
    {
        $place = $this->createPlace('Blocked place');
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-12',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);

        Livewire::test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15')
            ->assertSee(__('listing.detail.booking.unavailable_title'));
    }

    public function test_favorite_toggles_for_sleeping_place(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Favorite place');

        Livewire::actingAs($guest)
            ->test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->call('toggleFavorite')
            ->assertSet('isFavorited', true);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
            'bed_id' => null,
        ]);

        Livewire::actingAs($guest)
            ->test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->call('toggleFavorite')
            ->assertSet('isFavorited', false);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
        ]);
    }

    public function test_host_card_is_shown(): void
    {
        $place = $this->createPlace('Hosted place', hostName: 'Mila Host');

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee('Mila Host')
            ->assertSee(__('listing.detail.host.title'));
    }

    public function test_privacy_safe_occupant_summary_is_shown(): void
    {
        $place = $this->createPlace('Privacy place');
        $otherGuest = User::factory()->create(['name' => 'Private Guest Name']);
        $otherPlace = SleepingPlace::factory()
            ->for($place->room)
            ->for($place->property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
            ]);

        Booking::factory()->create([
            'guest_user_id' => $otherGuest->id,
            'host_user_id' => $place->property->host_user_id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $otherPlace->id,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-15',
            'status' => BookingStatus::Confirmed,
        ]);

        $this->get(route('places.show', [
            'locale' => 'en',
            'sleepingPlace' => $place,
            'in' => '2026-07-11',
            'out' => '2026-07-13',
        ]))
            ->assertOk()
            ->assertSee('1 guest nearby')
            ->assertSee(__('listing.detail.nearby.privacy'))
            ->assertDontSee('Private Guest Name');
    }

    private function createPlace(string $title, ?string $ruTitle = null, string $hostName = 'Nina Host'): SleepingPlace
    {
        $city = $this->city('Vilnius');
        $host = User::factory()->create(['name' => $hostName, 'is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'display_name' => $hostName,
            'rating_average' => 4.8,
            'reviews_count' => 8,
            'verified_at' => now(),
        ]);
        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $city->country_id,
                'region_id' => $city->region_id,
                'city_id' => $city->id,
                'city' => $city->name,
                'district' => 'Old Town',
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
                'nearest_transport' => 'Bus stop nearby',
                'kitchens_count' => 1,
                'bathrooms_count' => 1,
                'showers_count' => 1,
            ]);
        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Shared apartment',
            'summary' => 'A calm apartment near the center.',
            'description' => 'A calm apartment near the center.',
        ]);
        $property->translations()->create([
            'locale' => 'ru',
            'title' => 'Общая квартира',
            'summary' => 'Спокойная квартира рядом с центром.',
            'description' => 'Спокойная квартира рядом с центром.',
        ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::NoRestriction,
            'beds_count' => 4,
            'max_guests' => 4,
            'occupied_places_count' => 0,
            'noise_level' => 'quiet',
        ]);
        $room->translations()->create([
            'locale' => 'en',
            'title' => 'Shared quiet room',
            'summary' => 'A quiet shared room.',
            'description' => 'A quiet shared room.',
        ]);
        $room->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихая общая комната',
            'summary' => 'Тихая общая комната.',
            'description' => 'Тихая общая комната.',
        ]);

        $quietRule = $this->rule('quiet_hours_after_22', 'quiet_hours', 'Quiet hours after 22:00', 'Тихие часы после 22:00');
        $room->rules()->attach($quietRule);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::BunkBottom,
                'display_name' => $title,
                'base_price_per_night' => 30,
                'cleaning_fee' => 5,
                'deposit_amount' => 30,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => null,
                'has_bedding' => true,
                'has_towel' => true,
                'has_locker' => true,
                'privacy_level' => 'moderate',
            ]);
        $place->translations()->create([
            'locale' => 'en',
            'title' => $title,
            'summary' => 'A comfortable lower bunk.',
            'description' => 'A comfortable lower bunk.',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => $ruTitle ?: $title,
            'summary' => 'Удобное нижнее место.',
            'description' => 'Удобное нижнее место.',
        ]);

        return $place;
    }

    private function city(string $name): City
    {
        $country = Country::query()->firstOrCreate(
            ['iso2' => 'LT'],
            [
                'code' => 'LT',
                'iso3' => 'LTU',
                'name' => 'Lithuania',
                'name_en' => 'Lithuania',
                'status' => Country::STATUS_ACTIVE,
                'is_active' => true,
            ],
        );
        $region = Region::factory()->for($country)->create([
            'name' => 'Vilnius County',
        ]);

        return City::factory()->for($country)->for($region)->create([
            'name' => $name,
            'ascii_name' => $name,
            'status' => City::STATUS_ACTIVE,
            'is_active' => true,
        ]);
    }

    private function rule(string $slug, string $category, string $en, string $ru): Rule
    {
        $rule = Rule::factory()->create([
            'slug' => $slug,
            'category' => $category,
            'status' => 'active',
            'name_normalized' => str($en)->lower()->toString(),
        ]);

        $rule->translations()->create(['locale' => 'en', 'name' => $en, 'name_normalized' => str($en)->lower()->toString()]);
        $rule->translations()->create(['locale' => 'ru', 'name' => $ru, 'name_normalized' => str($ru)->lower()->toString()]);

        return $rule;
    }
}
