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
use App\Livewire\SavedSearches\SavedSearchesPage;
use App\Livewire\SavedSearches\SavedSearchPage;
use App\Livewire\SavedSearches\SaveSearchButton;
use App\Models\Amenity;
use App\Models\AvailabilityDay;
use App\Models\City;
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\SavedSearch;
use App\Models\SavedSearchResult;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchFrequencyService;
use App\Services\SavedSearches\SavedSearchService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class SavedSearchesFeatureTest extends TestCase
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

    public function test_saved_search_service_creates_updates_pauses_resumes_archives_and_deletes(): void
    {
        $guest = User::factory()->create();
        $city = $this->city('Vilnius');
        $service = app(SavedSearchService::class);

        $search = $service->create($guest, [
            'title' => 'Vilnius week under 30',
            'description' => 'Need Wi-Fi and a locker',
            'city_id' => $city->id,
            'district' => 'Old Town',
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-17',
            'guests_count' => 1,
            'budget_max' => 30,
            'currency' => 'EUR',
            'require_wifi' => true,
            'require_locker' => true,
            'only_instant_booking' => true,
            'only_verified_hosts' => true,
            'notification_frequency' => 'daily',
        ]);

        $this->assertSame('Vilnius week under 30', $search->title);
        $this->assertSame('Vilnius week under 30', $search->name);
        $this->assertSame(7, $search->nights_count);
        $this->assertSame(8, $search->calendar_days_count);
        $this->assertTrue($search->require_wifi);
        $this->assertTrue($search->notify_new_matches);

        $service->update($guest, $search, ['title' => 'Updated search', 'budget_max' => 25]);

        $this->assertSame('Updated search', $search->refresh()->title);
        $this->assertSame('Updated search', $search->name);
        $this->assertSame('25.00', (string) $search->budget_max);

        $service->pause($guest, $search);
        $this->assertSame('paused', $search->refresh()->status);
        $this->assertFalse($search->is_active);

        $service->resume($guest, $search);
        $this->assertSame('active', $search->refresh()->status);
        $this->assertTrue($search->is_active);

        $service->archive($guest, $search);
        $this->assertSame('archived', $search->refresh()->status);

        $service->delete($guest, $search);
        $this->assertModelMissing($search);
    }

    public function test_run_now_finds_indexed_matches_and_prevents_duplicate_results(): void
    {
        $guest = User::factory()->create();
        $city = $this->city('Vilnius');
        $wifi = $this->amenity('wifi');
        $match = $this->place('Matching place', $city, [
            'base_price_per_night' => 22,
            'instant_booking_enabled' => true,
            'has_locker' => true,
        ]);
        $match->property->amenities()->attach($wifi);
        $this->place('Wrong city place', $this->city('Riga'), ['base_price_per_night' => 20, 'instant_booking_enabled' => true]);
        $this->place('Too expensive place', $city, ['base_price_per_night' => 80, 'instant_booking_enabled' => true]);

        $search = SavedSearch::factory()->for($guest)->for($city)->create([
            'title' => 'Strict Vilnius',
            'name' => 'Strict Vilnius',
            'status' => 'active',
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'budget_max' => 30,
            'price_max' => 30,
            'currency' => 'EUR',
            'require_wifi' => true,
            'require_locker' => true,
            'only_instant_booking' => true,
            'only_verified_hosts' => true,
        ]);

        $first = app(SavedSearchService::class)->runNow($guest, $search);
        $second = app(SavedSearchService::class)->runNow($guest, $search->refresh());

        $this->assertSame(1, $first->matchedCount);
        $this->assertSame(1, $second->matchedCount);
        $this->assertSame(1, SavedSearchResult::query()->whereBelongsTo($search)->count());
        $this->assertDatabaseHas('saved_search_results', [
            'saved_search_id' => $search->id,
            'sleeping_place_id' => $match->id,
            'is_new_match' => true,
        ]);
        $this->assertSame(1, $search->refresh()->new_matches_count);
        $this->assertNotNull($search->last_checked_at);
        $this->assertNotNull($search->next_check_at);
    }

    public function test_price_drop_available_again_and_notifications_are_detected(): void
    {
        $guest = User::factory()->create();
        $city = $this->city('Vilnius');
        $place = $this->place('Changing result', $city, [
            'base_price_per_night' => 40,
            'instant_booking_enabled' => true,
        ]);
        $search = SavedSearch::factory()->for($guest)->for($city)->create([
            'status' => 'active',
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'budget_max' => 60,
            'price_max' => 60,
            'notify_new_matches' => true,
            'notify_price_drops' => true,
            'notify_available_again' => true,
            'notification_frequency' => 'on_visit',
        ]);

        app(SavedSearchService::class)->runNow($guest, $search);

        $result = SavedSearchResult::query()->whereBelongsTo($search)->whereBelongsTo($place)->firstOrFail();

        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);
        app(SavedSearchService::class)->runNow($guest, $search->refresh());

        $result->refresh();
        $this->assertTrue($result->became_unavailable);

        $place->update(['base_price_per_night' => 25]);
        AvailabilityDay::query()->where('sleeping_place_id', $place->id)->delete();

        app(SavedSearchService::class)->runNow($guest, $search->refresh());

        $result->refresh();
        $this->assertTrue($result->price_changed);
        $this->assertLessThan(0, (float) $result->price_change_amount);
        $this->assertTrue($result->became_available_again);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'saved_search_price_drop',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'saved_search_available_again',
        ]);
    }

    public function test_frequency_service_respects_daily_weekly_on_visit_and_quiet_hours(): void
    {
        $search = SavedSearch::factory()->create([
            'notification_frequency' => 'daily',
            'last_checked_at' => CarbonImmutable::now()->subHours(23),
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
        ]);
        $frequency = app(SavedSearchFrequencyService::class);

        $this->assertFalse($frequency->shouldCheck($search));

        $search->forceFill(['last_checked_at' => CarbonImmutable::now()->subDay()->subMinute()])->save();
        $this->assertTrue($frequency->shouldCheck($search->refresh()));

        $search->forceFill(['notification_frequency' => 'weekly', 'last_checked_at' => CarbonImmutable::now()->subDays(6)])->save();
        $this->assertFalse($frequency->shouldCheck($search->refresh()));

        $search->forceFill(['notification_frequency' => 'on_visit'])->save();
        $this->assertTrue($frequency->shouldCheck($search->refresh()));
    }

    public function test_livewire_pages_save_button_and_access_policy(): void
    {
        $guest = User::factory()->create();
        $other = User::factory()->create();
        $city = $this->city('Vilnius');
        $place = $this->place('Page result', $city, ['base_price_per_night' => 20]);
        $search = SavedSearch::factory()->for($guest)->for($city)->create([
            'title' => 'Page search',
            'name' => 'Page search',
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'budget_max' => 30,
        ]);

        app(SavedSearchService::class)->runNow($guest, $search);

        Livewire::actingAs($guest)
            ->test(SavedSearchesPage::class)
            ->assertSee(__('saved_searches.title'))
            ->assertSee('Page search');

        Livewire::actingAs($guest)
            ->test(SavedSearchPage::class, ['savedSearch' => $search])
            ->assertSee('Page search')
            ->assertSee('Page result')
            ->call('pause')
            ->assertHasNoErrors()
            ->call('resume')
            ->assertHasNoErrors();

        Livewire::actingAs($guest)
            ->test(SaveSearchButton::class, [
                'cityId' => $city->id,
                'cityName' => 'Vilnius',
                'checkIn' => '2026-07-10',
                'checkOut' => '2026-07-13',
                'priceMax' => '30',
                'instantBooking' => true,
            ])
            ->call('open')
            ->set('title', 'Saved from search')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saved_searches', [
            'user_id' => $guest->id,
            'title' => 'Saved from search',
            'city_id' => $city->id,
        ]);

        $this->assertTrue(Gate::forUser($guest)->allows('view', $search));
        $this->assertFalse(Gate::forUser($other)->allows('view', $search));

        $this->actingAs($other)
            ->get(route('saved-searches.show', ['locale' => 'en', 'savedSearch' => $search]))
            ->assertForbidden();
    }

    public function test_saved_search_pages_render_in_english_and_russian(): void
    {
        $guest = User::factory()->create();

        $this->actingAs($guest)
            ->get(route('saved-searches.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSee(__('saved_searches.title', [], 'en'));

        $this->actingAs($guest)
            ->get(route('saved-searches.index', ['locale' => 'ru']))
            ->assertOk()
            ->assertSee(__('saved_searches.title', [], 'ru'));
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

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function place(string $title, City $city, array $overrides = []): SleepingPlace
    {
        $host = User::factory()->create(['is_host' => true, 'identity_verified' => true]);
        HostProfile::factory()->for($host, 'user')->create([
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
                'kitchens_count' => 1,
            ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::NoRestriction,
            'has_desk' => true,
        ]);
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::Single,
                'display_name' => $title,
                'base_price_per_night' => 30,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => null,
                ...$overrides,
            ]);

        $place->translations()->create(['locale' => 'en', 'title' => $title, 'summary' => $title]);
        $place->translations()->create(['locale' => 'ru', 'title' => 'RU '.$title, 'summary' => 'RU '.$title]);

        return $place;
    }

    private function amenity(string $slug): Amenity
    {
        $amenity = Amenity::factory()->create([
            'slug' => $slug,
            'name_normalized' => $slug,
            'category' => 'property',
            'status' => 'active',
        ]);

        $amenity->translations()->create([
            'locale' => 'en',
            'name' => str($slug)->replace('_', ' ')->title()->toString(),
            'name_normalized' => str($slug)->replace('_', ' ')->lower()->toString(),
        ]);
        $amenity->translations()->create([
            'locale' => 'ru',
            'name' => 'RU '.str($slug)->replace('_', ' ')->title()->toString(),
            'name_normalized' => 'ru '.str($slug)->replace('_', ' ')->lower()->toString(),
        ]);

        return $amenity;
    }
}
