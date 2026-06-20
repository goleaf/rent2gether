<?php

namespace Tests\Feature;

use App\Actions\DecisionTools\GenerateDecisionToolNotifications;
use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Compare\ComparePlaces;
use App\Livewire\Places\ShowSleepingPlace;
use App\Livewire\SavedSearches\SavedSearchesList;
use App\Livewire\Waitlist\MyWaitlist;
use App\Models\City;
use App\Models\Country;
use App\Models\Favorite;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuestDecisionToolsTest extends TestCase
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

    public function test_favorite_toggle_saves_selected_dates_and_price(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Favorite decision place');

        Livewire::actingAs($guest)
            ->test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15')
            ->set('guestsCount', 1)
            ->call('toggleFavorite')
            ->assertSet('isFavorited', true);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
            'check_in' => '2026-07-10 00:00:00',
            'check_out' => '2026-07-15 00:00:00',
            'guests_count' => 1,
            'notify_available' => true,
            'notify_price_drop' => true,
        ]);

        $this->assertNotNull(Favorite::query()->firstWhere('sleeping_place_id', $place->id)?->price_at_save);
    }

    public function test_saved_search_create_update_and_delete(): void
    {
        $guest = User::factory()->create();
        $city = $this->city('Vilnius');

        $component = Livewire::actingAs($guest)
            ->test(SavedSearchesList::class)
            ->set('name', 'Quiet Vilnius beds')
            ->set('cityQuery', 'Vilnius')
            ->call('selectCity', $city->id)
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15')
            ->set('flexibleDates', true)
            ->set('priceMin', '10')
            ->set('priceMax', '50')
            ->set('filters.wifi', true)
            ->call('save')
            ->assertHasNoErrors();

        $search = $guest->savedSearches()->firstOrFail();

        $this->assertSame($city->id, $search->city_id);
        $this->assertTrue($search->flexible_dates);
        $this->assertSame(['wifi' => true], $search->filters_json);

        $component
            ->call('edit', $search->id)
            ->set('name', 'Updated Vilnius beds')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saved_searches', [
            'id' => $search->id,
            'name' => 'Updated Vilnius beds',
        ]);

        $component->call('delete', $search->id);

        $this->assertDatabaseMissing('saved_searches', [
            'id' => $search->id,
        ]);
    }

    public function test_waitlist_create_and_delete(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Waitlist place');

        $component = Livewire::actingAs($guest)
            ->test(MyWaitlist::class)
            ->set('sleepingPlaceId', $place->id)
            ->set('desiredCheckIn', '2026-07-10')
            ->set('desiredCheckOut', '2026-07-12')
            ->set('maxPrice', '35')
            ->set('notifyAvailable', true)
            ->set('notifyPriceDrop', true)
            ->call('save')
            ->assertHasNoErrors();

        $item = WaitlistItem::query()->firstOrFail();

        $this->assertDatabaseHas('waitlist_items', [
            'id' => $item->id,
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
            'desired_check_in' => '2026-07-10 00:00:00',
            'desired_check_out' => '2026-07-12 00:00:00',
            'notify_available' => true,
            'notify_price_drop' => true,
            'status' => 'waiting',
        ]);

        $component->call('remove', $item->id);

        $this->assertDatabaseHas('waitlist_items', [
            'id' => $item->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_compare_page_limits_selected_places_to_four(): void
    {
        $guest = User::factory()->create();
        $places = collect(range(1, 5))->map(fn (int $number): SleepingPlace => $this->createPlace('Compare place '.$number));

        $this->actingAs($guest)
            ->get(route('compare.index', [
                'locale' => 'en',
                'places' => $places->pluck('id')->implode(','),
                'in' => '2026-07-10',
                'out' => '2026-07-12',
            ]))
            ->assertOk()
            ->assertSeeLivewire(ComparePlaces::class)
            ->assertSee('Compare place 1')
            ->assertSee('Compare place 4')
            ->assertDontSee('Compare place 5');
    }

    public function test_decision_pages_render_localized_labels(): void
    {
        $guest = User::factory()->create();

        foreach (['en', 'ru'] as $locale) {
            $this->actingAs($guest)
                ->get(route('favorites.index', ['locale' => $locale]))
                ->assertOk()
                ->assertSee(__('shell.pages.guest.favorites.title', [], $locale))
                ->assertSee(__('decision.favorites.empty_title', [], $locale));

            $this->actingAs($guest)
                ->get(route('saved-searches.index', ['locale' => $locale]))
                ->assertOk()
                ->assertSee(__('decision.saved.title', [], $locale));

            $this->actingAs($guest)
                ->get(route('waitlist.index', ['locale' => $locale]))
                ->assertOk()
                ->assertSee(__('decision.waitlist.title', [], $locale));

            $this->actingAs($guest)
                ->get(route('compare.index', ['locale' => $locale]))
                ->assertOk()
                ->assertSee(__('decision.compare.title', [], $locale));
        }
    }

    public function test_decision_notifications_are_generated(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Notification place', ['base_price_per_night' => 20, 'cleaning_fee' => 0, 'deposit_amount' => 0]);

        Favorite::factory()->create([
            'user_id' => $guest->id,
            'bed_id' => null,
            'sleeping_place_id' => $place->id,
            'price_at_save' => 40,
            'check_in' => null,
            'check_out' => null,
            'notify_price_drop' => true,
        ]);

        WaitlistItem::factory()->create([
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
            'desired_check_in' => '2026-07-10',
            'desired_check_out' => '2026-07-12',
            'price_at_join' => null,
            'notify_available' => true,
            'notify_price_drop' => false,
            'notified' => false,
            'status' => 'waiting',
        ]);

        $created = app(GenerateDecisionToolNotifications::class)->handle();

        $this->assertSame(2, $created);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'decision.favorite_price_drop',
            'title_key' => 'notifications.decision.favorite_price_drop.title',
            'status' => 'unread',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'decision.waitlist_available',
            'title_key' => 'notifications.decision.waitlist_available.title',
            'status' => 'unread',
        ]);
    }

    /**
     * @param  array<string, mixed>  $placeOverrides
     */
    private function createPlace(string $title, array $placeOverrides = []): SleepingPlace
    {
        $city = $this->city('Vilnius');
        $host = User::factory()->create(['is_host' => true]);
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
                'kitchens_count' => 1,
                'amenities' => ['wifi', 'kitchen'],
            ]);
        $room = Room::factory()
            ->for($property)
            ->create([
                'status' => RoomStatus::Active,
                'type' => RoomType::Shared,
                'gender_policy' => GenderType::NoRestriction,
                'occupied_places_count' => 1,
            ]);
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'base_price_per_night' => 30,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                ...$placeOverrides,
            ]);

        $place->translations()->create([
            'locale' => 'en',
            'title' => $title,
            'summary' => $title,
            'description' => $title,
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'RU '.$title,
            'summary' => 'RU '.$title,
            'description' => 'RU '.$title,
        ]);

        return $place;
    }

    private function city(string $name): City
    {
        $country = Country::factory()->create(['name_en' => 'Lithuania']);
        $region = Region::factory()->for($country)->create(['name' => $name.' County']);

        return City::factory()
            ->for($country)
            ->for($region)
            ->create([
                'name' => $name,
                'ascii_name' => $name,
                'population' => 500000,
                'status' => City::STATUS_ACTIVE,
                'is_active' => true,
            ]);
    }
}
