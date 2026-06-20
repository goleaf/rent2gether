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
use App\Livewire\Host\PropertyList;
use App\Livewire\Host\PropertyShow;
use App\Livewire\Host\SleepingPlaceList;
use App\Livewire\Places\ShowSleepingPlace;
use App\Livewire\Shell\HostListingsPage;
use App\Models\Amenity;
use App\Models\AvailabilityDay;
use App\Models\City;
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostListings\HostListingDashboardService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class HostListingsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-20 09:00:00');
        CarbonImmutable::setTestNow('2026-06-20 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_host_sees_own_listings_only(): void
    {
        $host = User::factory()->create(['is_host' => true]);
        $otherHost = User::factory()->create(['is_host' => true]);

        $this->createListing($host, propertyTitle: 'Host A home', placeTitle: 'Host A lower bed', complete: true);
        $this->createListing($otherHost, propertyTitle: 'Other host home', placeTitle: 'Other host bed', complete: true);

        $this->actingAs($host)
            ->get(route('host.listings.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSeeLivewire(HostListingsPage::class)
            ->assertSee('Host A home')
            ->assertDontSee('Other host home')
            ->assertSee(Lang::get('host.listings.readiness.label', [], 'en'));
    }

    public function test_readiness_and_tips_are_calculated(): void
    {
        app()->setLocale('en');

        $incompleteHost = User::factory()->create(['is_host' => true]);
        $completeHost = User::factory()->create(['is_host' => true]);
        $incomplete = $this->createListing($incompleteHost, propertyTitle: 'Needs work home', placeTitle: 'Needs work bed', withRussianTranslation: false);
        $complete = $this->createListing($completeHost, propertyTitle: 'Ready home', placeTitle: 'Ready lower bed', complete: true);

        $incompleteCards = collect(app(HostListingDashboardService::class)->propertyCards($incompleteHost));
        $completeCards = collect(app(HostListingDashboardService::class)->propertyCards($completeHost));

        $incompleteCard = $incompleteCards->firstWhere('id', $incomplete['property']->id);
        $completeCard = $completeCards->firstWhere('id', $complete['property']->id);

        $this->assertSame(45, $incompleteCard['readiness']);
        $this->assertContains('exact_sleeping_place_photo', array_column($incompleteCard['tips'], 'key'));
        $this->assertContains('complete_ru', array_column($incompleteCard['tips'], 'key'));

        $this->assertSame(100, $completeCard['readiness']);
        $this->assertContains('ready', array_column($completeCard['tips'], 'key'));
    }

    public function test_draft_and_hidden_listing_scopes_render(): void
    {
        $host = User::factory()->create(['is_host' => true]);

        $this->createListing(
            $host,
            propertyTitle: 'Draft nest',
            placeTitle: 'Draft bed',
            propertyStatus: PropertyStatus::Draft,
            roomStatus: RoomStatus::Draft,
            placeStatus: SleepingPlaceStatus::Draft,
        );
        $this->createListing(
            $host,
            propertyTitle: 'Hidden nest',
            placeTitle: 'Hidden bed',
            propertyStatus: PropertyStatus::Hidden,
            placeStatus: SleepingPlaceStatus::Hidden,
        );

        $this->actingAs($host)
            ->get(route('host.listings.scope', ['locale' => 'en', 'scope' => 'drafts']))
            ->assertOk()
            ->assertSee('Draft nest')
            ->assertDontSee('Hidden nest');

        $this->actingAs($host)
            ->get(route('host.listings.scope', ['locale' => 'en', 'scope' => 'hidden']))
            ->assertOk()
            ->assertSee('Hidden nest')
            ->assertDontSee('Draft nest');
    }

    public function test_host_listing_subpages_render(): void
    {
        $host = User::factory()->create(['is_host' => true]);
        $listing = $this->createListing($host, propertyTitle: 'Subpage property', placeTitle: 'Subpage bed', complete: true);

        $this->actingAs($host)
            ->get(route('host.properties.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSeeLivewire(PropertyList::class)
            ->assertSee('Subpage property');

        $this->actingAs($host)
            ->get(route('host.properties.show', ['locale' => 'en', 'property' => $listing['property']]))
            ->assertOk()
            ->assertSeeLivewire(PropertyShow::class)
            ->assertSee('Subpage property')
            ->assertSee('Shared room for Subpage property');

        $this->actingAs($host)
            ->get(route('host.sleeping-places.index', ['locale' => 'en', 'room' => $listing['room']]))
            ->assertOk()
            ->assertSeeLivewire(SleepingPlaceList::class)
            ->assertSee('Subpage bed')
            ->assertSee(Lang::get('host.listings.readiness.label', [], 'en'));
    }

    public function test_public_listing_visibility_follows_status(): void
    {
        $host = User::factory()->create(['is_host' => true]);
        $draft = $this->createListing(
            $host,
            propertyTitle: 'Draft public property',
            placeTitle: 'Draft public bed',
            propertyStatus: PropertyStatus::Draft,
            roomStatus: RoomStatus::Draft,
            placeStatus: SleepingPlaceStatus::Draft,
        );
        $active = $this->createListing($host, propertyTitle: 'Active public property', placeTitle: 'Public lower bed', complete: true);

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $draft['place']]))
            ->assertNotFound();

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $active['place']]))
            ->assertOk()
            ->assertSeeLivewire(ShowSleepingPlace::class)
            ->assertSee('Public lower bed');
    }

    public function test_localized_host_dashboard_renders(): void
    {
        $host = User::factory()->create(['is_host' => true]);

        $this->createListing($host, propertyTitle: 'Localized home', placeTitle: 'Localized bed', complete: true);

        $this->actingAs($host)
            ->get(route('host.dashboard', ['locale' => 'ru']))
            ->assertOk()
            ->assertSee('Готовность')
            ->assertSee('Советы для улучшения объекта')
            ->assertSee('Недавние объекты');
    }

    /**
     * @return array{property: Property, room: Room, place: SleepingPlace}
     */
    private function createListing(
        User $host,
        string $propertyTitle,
        string $placeTitle,
        PropertyStatus $propertyStatus = PropertyStatus::Active,
        RoomStatus $roomStatus = RoomStatus::Active,
        SleepingPlaceStatus $placeStatus = SleepingPlaceStatus::Active,
        bool $withRussianTranslation = true,
        bool $complete = false,
    ): array {
        [$country, $region, $city] = $this->geo();

        if ($complete && ! HostProfile::query()->where('user_id', $host->id)->exists()) {
            HostProfile::factory()->for($host, 'user')->create([
                'display_name' => $host->name,
                'default_cancellation_policy' => 'flexible',
            ]);
        }

        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $country->id,
                'region_id' => $region->id,
                'city_id' => $city->id,
                'title' => $propertyTitle,
                'description' => 'A clear property description.',
                'country' => 'Lithuania',
                'city' => $city->name,
                'district' => 'Old Town',
                'street' => 'Pilies',
                'address_line_1' => 'Pilies 10',
                'type' => PropertyType::Apartment,
                'status' => $propertyStatus,
            ]);

        $property->translations()->create([
            'locale' => 'en',
            'title' => $propertyTitle,
            'summary' => 'A calm shared stay.',
            'description' => 'A clear property description.',
            'check_in_instructions' => $complete ? 'Use the key box near the door.' : null,
            'house_rules_text' => $complete ? 'Quiet hours after 22:00.' : null,
            'safety_notes' => 'Common areas are lit.',
        ]);

        if ($withRussianTranslation) {
            $property->translations()->create([
                'locale' => 'ru',
                'title' => $propertyTitle.' RU',
                'summary' => 'Спокойное общее проживание.',
                'description' => 'Понятное описание объекта.',
                'check_in_instructions' => $complete ? 'Используйте бокс для ключей у двери.' : null,
                'house_rules_text' => $complete ? 'Тихие часы после 22:00.' : null,
                'safety_notes' => 'Общие зоны освещены.',
            ]);
        }

        $room = Room::factory()->for($property)->create([
            'title' => 'Shared room for '.$propertyTitle,
            'status' => $roomStatus,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::NoRestriction,
            'beds_count' => 4,
            'max_guests' => 4,
            'noise_level' => 'quiet',
        ]);

        $room->translations()->create([
            'locale' => 'en',
            'title' => 'Shared room for '.$propertyTitle,
            'summary' => 'A quiet shared room.',
            'description' => 'A quiet shared room.',
            'notes' => 'Keep personal items tidy.',
        ]);
        $room->translations()->create([
            'locale' => 'ru',
            'title' => 'Общая комната',
            'summary' => 'Тихая общая комната.',
            'description' => 'Тихая общая комната.',
            'notes' => 'Держите личные вещи аккуратно.',
        ]);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => $placeStatus,
                'type' => SleepingPlaceType::BunkBottom,
                'display_name' => $placeTitle,
                'base_price_per_night' => 30,
                'weekly_price' => $complete ? 180 : null,
                'currency' => 'EUR',
                'has_towel' => true,
                'has_bedding' => true,
                'has_locker' => true,
            ]);

        $place->translations()->create([
            'locale' => 'en',
            'title' => $placeTitle,
            'summary' => 'A comfortable lower bed.',
            'description' => 'A comfortable lower bed with a locker.',
            'special_conditions' => 'Please keep the shared room quiet.',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => $placeTitle.' RU',
            'summary' => 'Удобное нижнее место.',
            'description' => 'Удобное нижнее место со шкафчиком.',
            'special_conditions' => 'Пожалуйста, соблюдайте тишину в общей комнате.',
        ]);

        if ($complete) {
            $amenity = Amenity::query()->firstOrCreate(
                ['slug' => 'wifi'],
                ['name_normalized' => 'wifi', 'category' => 'property', 'status' => 'active'],
            );
            $amenity->translations()->updateOrCreate(['locale' => 'en'], ['name' => 'Wi-Fi', 'name_normalized' => 'wi-fi']);
            $amenity->translations()->updateOrCreate(['locale' => 'ru'], ['name' => 'Wi-Fi', 'name_normalized' => 'wi-fi']);

            $rule = Rule::query()->firstOrCreate(
                ['slug' => 'quiet_hours'],
                ['category' => 'quiet_hours', 'name_normalized' => 'quiet hours', 'status' => 'active'],
            );
            $rule->translations()->updateOrCreate(['locale' => 'en'], ['name' => 'Quiet hours after 22:00', 'name_normalized' => 'quiet hours after 22:00']);
            $rule->translations()->updateOrCreate(['locale' => 'ru'], ['name' => 'Тихие часы после 22:00', 'name_normalized' => 'тихие часы после 22:00']);

            $property->amenities()->attach($amenity);
            $property->rules()->attach($rule);
            $room->rules()->attach($rule);
            $place->rules()->attach($rule);

            $this->createMedia($property, $host, 'gallery');
            $this->createMedia($property, $host, 'bathroom');
            $this->createMedia($place, $host, 'gallery');

            foreach (range(0, 13) as $offset) {
                AvailabilityDay::factory()->for($place)->create([
                    'date' => CarbonImmutable::today()->addDays(10 + $offset)->toDateString(),
                    'status' => AvailabilityStatus::Available,
                ]);
            }
        }

        return [
            'property' => $property,
            'room' => $room,
            'place' => $place,
        ];
    }

    /**
     * @return array{0: Country, 1: Region, 2: City}
     */
    private function geo(): array
    {
        $country = Country::query()->firstOrCreate(
            ['code' => 'LT'],
            [
                'iso2' => 'LT',
                'iso3' => 'LTU',
                'name' => 'Lithuania',
                'name_en' => 'Lithuania',
                'name_ru' => 'Литва',
                'status' => Country::STATUS_ACTIVE,
                'is_active' => true,
            ],
        );

        $region = Region::query()->firstOrCreate(
            ['country_id' => $country->id, 'code' => 'VL'],
            [
                'name' => 'Vilnius County',
                'source' => 'geonames',
                'source_id' => '864389',
            ],
        );

        $city = City::query()->firstOrCreate(
            ['country_id' => $country->id, 'name' => 'Vilnius'],
            [
                'region_id' => $region->id,
                'geoname_id' => 593116,
                'ascii_name' => 'Vilnius',
                'latitude' => 54.68916,
                'longitude' => 25.2798,
                'population' => 542366,
                'timezone' => 'Europe/Vilnius',
                'feature_class' => 'P',
                'feature_code' => 'PPL',
                'status' => City::STATUS_ACTIVE,
                'is_active' => true,
            ],
        );

        return [$country, $region, $city];
    }

    private function createMedia(Model $model, User $host, string $collection): MediaItem
    {
        return MediaItem::factory()->create([
            'owner_type' => $model::class,
            'owner_id' => $model->getKey(),
            'owner_user_id' => $host->id,
            'mediable_type' => $model::class,
            'mediable_id' => $model->getKey(),
            'collection' => $collection,
            'status' => 'active',
        ]);
    }
}
