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
        config([
            'localization.supported_locales' => ['en', 'ru', 'de'],
            'localization.locale_names.de' => 'German',
        ]);

        $incompleteHost = User::factory()->create(['is_host' => true]);
        $completeHost = User::factory()->create(['is_host' => true]);
        $incomplete = $this->createListing($incompleteHost, propertyTitle: 'Needs work home', placeTitle: 'Needs work bed', missingPropertyTranslationLocales: ['ru', 'de']);
        $complete = $this->createListing($completeHost, propertyTitle: 'Ready home', placeTitle: 'Ready lower bed', complete: true);

        $incompleteCards = collect(app(HostListingDashboardService::class)->propertyCards($incompleteHost));
        $completeCards = collect(app(HostListingDashboardService::class)->propertyCards($completeHost));

        $incompleteCard = $incompleteCards->firstWhere('id', $incomplete['property']->id);
        $completeCard = $completeCards->firstWhere('id', $complete['property']->id);

        $incompleteTipKeys = array_column($incompleteCard['tips'], 'key');

        $this->assertSame(36, $incompleteCard['readiness']);
        $this->assertContains('exact_sleeping_place_photo', $incompleteTipKeys);
        $this->assertContains('missing_translations', $incompleteTipKeys);
        $this->assertNotContains('complete_ru', $incompleteTipKeys);
        $this->assertNotContains('complete_en', $incompleteTipKeys);

        $translationTip = collect($incompleteCard['tips'])->firstWhere('key', 'missing_translations');
        $this->assertSame(2, $translationTip['params']['count']);
        $this->assertSame('Russian, German', $translationTip['params']['locales']);

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
        array $missingPropertyTranslationLocales = [],
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

        foreach ($this->supportedTestLocales() as $locale) {
            if (in_array($locale, $missingPropertyTranslationLocales, true)) {
                continue;
            }

            $property->translations()->create([
                'locale' => $locale,
                'title' => $locale === 'en' ? $propertyTitle : $propertyTitle.' '.strtoupper($locale),
                'summary' => $this->localizedFixture($locale, 'A calm shared stay.', ['ru' => 'Спокойное общее проживание.']),
                'description' => $this->localizedFixture($locale, 'A clear property description.', ['ru' => 'Понятное описание объекта.']),
                'check_in_instructions' => $complete
                    ? $this->localizedFixture($locale, 'Use the key box near the door.', ['ru' => 'Используйте бокс для ключей у двери.'])
                    : null,
                'house_rules_text' => $complete
                    ? $this->localizedFixture($locale, 'Quiet hours after 22:00.', ['ru' => 'Тихие часы после 22:00.'])
                    : null,
                'safety_notes' => $this->localizedFixture($locale, 'Common areas are lit.', ['ru' => 'Общие зоны освещены.']),
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

        foreach ($this->supportedTestLocales() as $locale) {
            $room->translations()->create([
                'locale' => $locale,
                'title' => $locale === 'en' ? 'Shared room for '.$propertyTitle : 'Shared room '.$locale,
                'summary' => $this->localizedFixture($locale, 'A quiet shared room.', ['ru' => 'Тихая общая комната.']),
                'description' => $this->localizedFixture($locale, 'A quiet shared room.', ['ru' => 'Тихая общая комната.']),
                'notes' => $this->localizedFixture($locale, 'Keep personal items tidy.', ['ru' => 'Держите личные вещи аккуратно.']),
            ]);
        }

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

        foreach ($this->supportedTestLocales() as $locale) {
            $place->translations()->create([
                'locale' => $locale,
                'title' => $locale === 'en' ? $placeTitle : $placeTitle.' '.strtoupper($locale),
                'summary' => $this->localizedFixture($locale, 'A comfortable lower bed.', ['ru' => 'Удобное нижнее место.']),
                'description' => $this->localizedFixture($locale, 'A comfortable lower bed with a locker.', ['ru' => 'Удобное нижнее место со шкафчиком.']),
                'special_conditions' => $this->localizedFixture($locale, 'Please keep the shared room quiet.', ['ru' => 'Пожалуйста, соблюдайте тишину в общей комнате.']),
            ]);
        }

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

    /**
     * @return list<string>
     */
    private function supportedTestLocales(): array
    {
        return collect(config('localization.supported_locales', [(string) config('app.fallback_locale', 'en')]))
            ->filter(fn (mixed $locale): bool => is_string($locale) && $locale !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $translations
     */
    private function localizedFixture(string $locale, string $fallback, array $translations): string
    {
        return $translations[$locale] ?? $fallback;
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
