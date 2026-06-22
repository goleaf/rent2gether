<?php

namespace Tests\Feature;

use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Search\SleepingPlaceSearch;
use App\Models\City;
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class MobilePerformanceBudgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-20 12:00:00');
        CarbonImmutable::setTestNow('2026-06-20 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_search_first_html_does_not_ship_hidden_filters_or_full_city_list(): void
    {
        City::factory()->for($this->country())->create([
            'name' => 'Slowtown Full City List',
            'ascii_name' => 'Slowtown Full City List',
        ]);

        $response = $this->get(route('search.index', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSeeLivewire(SleepingPlaceSearch::class);
        $response->assertSee(__('search.actions.open_filters', ['count' => 0]));
        $response->assertDontSee(__('search.filter_groups.comfort'));
        $response->assertDontSee(__('search.filters_flags.instant_booking'));
        $response->assertDontSee('Slowtown Full City List');
    }

    public function test_search_first_render_query_count_stays_bounded_for_initial_cards(): void
    {
        $city = $this->city('Vilnius');

        foreach (range(1, 13) as $number) {
            $this->createSearchPlace('Performance Place '.str_pad((string) $number, 2, '0', STR_PAD_LEFT), $city);
        }

        $queries = 0;
        DB::listen(static function () use (&$queries): void {
            $queries++;
        });

        $response = $this->get(route('search.index', ['locale' => 'en', 'city' => $city->id]));

        $response->assertOk();
        $response->assertSee('Performance Place 13');
        $response->assertDontSee('Performance Place 01');
        $this->assertLessThanOrEqual(35, $queries, 'Search initial render should keep query count bounded for 12 cards plus lookahead.');
    }

    public function test_search_cards_use_mobile_media_variant(): void
    {
        $city = $this->city('Riga');
        $place = $this->createSearchPlace('Mobile Variant Place', $city);
        MediaItem::factory()->create([
            'owner_type' => SleepingPlace::class,
            'owner_id' => $place->id,
            'mediable_type' => SleepingPlace::class,
            'mediable_id' => $place->id,
            'owner_user_id' => $place->property->host_user_id,
            'path' => 'cards/original.jpg',
            'mobile_path' => 'cards/mobile.jpg',
            'thumb_path' => 'cards/thumb.jpg',
            'thumbnail_path' => 'cards/thumb.jpg',
            'full_path' => 'cards/full.jpg',
        ]);

        $response = $this->get(route('search.index', ['locale' => 'en', 'city' => $city->id]));

        $response->assertOk();
        $response->assertSee('/storage/cards/mobile.jpg');
        $response->assertDontSee('/storage/cards/full.jpg');
    }

    public function test_livewire_binding_rules_stay_mobile_friendly(): void
    {
        $violations = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            $contents = File::get($file->getPathname());
            preg_match_all('/wire:model\.live(?:\.debounce\.(\d+)ms)?/', $contents, $liveMatches, PREG_SET_ORDER);

            foreach ($liveMatches as $match) {
                if (! isset($match[1]) || (int) $match[1] < 500) {
                    $violations[] = $file->getRelativePathname().': live model without debounce >= 500ms';
                }
            }

            preg_match_all('/<(?:flux:textarea|textarea)\b[^>]*>/s', $contents, $textareaMatches);

            foreach ($textareaMatches[0] as $tag) {
                if (str_contains($tag, 'wire:model.live')) {
                    $violations[] = $file->getRelativePathname().': textarea should not use live binding';
                }
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_livewire_query_limits_stay_mobile_sized(): void
    {
        $violations = [];

        foreach (File::allFiles(app_path('Livewire')) as $file) {
            $contents = File::get($file->getPathname());
            preg_match_all('/->limit\((\d+)\)/', $contents, $limitMatches, PREG_SET_ORDER);

            foreach ($limitMatches as $match) {
                if ((int) $match[1] > 30) {
                    $violations[] = $file->getRelativePathname().': limit('.$match[1].') exceeds the mobile Livewire budget';
                }
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_search_component_keeps_public_state_compact(): void
    {
        $component = Livewire::test(SleepingPlaceSearch::class)
            ->set('cityQuery', 'Vi')
            ->assertOk();

        $encoded = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertLessThan(35_000, strlen($encoded), 'Search Livewire payload should stay below the mobile snapshot target.');
        $this->assertStringNotContainsString('Slowtown Full City List', $encoded);
    }

    private function country(): Country
    {
        return Country::query()->firstOrCreate(
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
    }

    private function city(string $name): City
    {
        return City::factory()->for($this->country())->create([
            'name' => $name,
            'ascii_name' => $name,
            'status' => City::STATUS_ACTIVE,
            'is_active' => true,
        ]);
    }

    private function createSearchPlace(string $title, City $city): SleepingPlace
    {
        $host = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'rating_average' => 4.8,
            'reviews_count' => 12,
            'verified_at' => now(),
        ]);

        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $city->country_id,
                'city_id' => $city->id,
                'city' => $city->name,
                'district' => 'Central',
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
            ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::NoRestriction,
            'max_guests' => 4,
            'available_places_count' => 1,
        ]);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::Single,
                'display_name' => $title,
                'base_price_per_night' => 30,
                'currency' => 'EUR',
                'max_guests' => 1,
                'min_nights' => 1,
                'max_nights' => null,
            ]);

        $place->translations()->create(['locale' => 'en', 'title' => $title]);
        $place->translations()->create(['locale' => 'ru', 'title' => $title]);

        return $place;
    }
}
