<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Livewire\Host\Listings\BeforePublishChecklist;
use App\Livewire\Host\Listings\CreateListingWizard;
use App\Livewire\Host\Listings\ListingDraftSaveIndicator;
use App\Livewire\Host\Listings\ListingReadinessChecklist;
use App\Livewire\Host\Listings\ListingWizardProgress;
use App\Models\AvailabilityDay;
use App\Models\City;
use App\Models\Country;
use App\Models\HostListingWizardSession;
use App\Models\HostProfile;
use App\Models\ListingPublicationCheck;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\Region;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use App\Services\HostListings\Wizard\HostListingPublishService;
use App\Services\HostListings\Wizard\HostListingReadinessService;
use App\Services\HostListings\Wizard\HostListingWizardService;
use App\Services\HostListings\Wizard\HostRoomDraftService;
use App\Services\HostListings\Wizard\HostSleepingPlaceDraftService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class HostListingWizardFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_tables_models_relationships_and_publication_columns_exist(): void
    {
        $this->assertTrue(Schema::hasTable('host_listing_wizard_sessions'));
        $this->assertTrue(Schema::hasTable('listing_publication_checks'));
        $this->assertTrue(Schema::hasColumn('properties', 'publication_status'));
        $this->assertTrue(Schema::hasColumn('properties', 'review_status'));
        $this->assertTrue(Schema::hasColumn('rooms', 'publication_status'));
        $this->assertTrue(Schema::hasColumn('sleeping_places', 'publication_status'));
        $this->assertTrue(Schema::hasColumn('sleeping_places', 'cleaning_gap_days'));
        $this->assertTrue(Schema::hasIndex('host_listing_wizard_sessions', ['user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('listing_publication_checks', ['property_id', 'status']));
        $this->assertTrue(Schema::hasIndex('listing_publication_checks', ['sleeping_place_id', 'status']));

        $listing = $this->listing();
        $session = HostListingWizardSession::factory()->for($listing['host'], 'user')->for($listing['property'])->create();
        $check = ListingPublicationCheck::factory()
            ->for($listing['host'], 'user')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create();

        $this->assertSame($listing['host']->id, $session->user->id);
        $this->assertSame($listing['property']->id, $session->property->id);
        $this->assertSame($listing['room']->id, $check->room->id);
        $this->assertSame($listing['place']->id, $check->sleepingPlace->id);
    }

    public function test_host_can_start_resume_and_track_wizard_progress(): void
    {
        $host = User::factory()->create(['is_host' => true]);
        $service = app(HostListingWizardService::class);

        $session = $service->start($host);

        $this->assertSame('property', $session->current_step);
        $this->assertSame('draft', $session->status);
        $this->assertNotNull($session->property_id);

        $property = $session->property;
        $service->markStepCompleted($property, 'property');
        $service->saveStep($host, $property, 'rooms', ['next_step' => 'sleeping_places']);

        $resumed = $service->resume($host, $property);
        $progress = $service->getProgress($property);

        $this->assertSame($session->id, $resumed->id);
        $this->assertSame('sleeping_places', $service->getCurrentStep($property));
        $this->assertContains('property', $progress['completed']);
        $this->assertSame(20, $progress['percentage']);
    }

    public function test_draft_services_create_rooms_places_and_calendar_dates(): void
    {
        $listing = $this->listing(place: false);
        $room = app(HostRoomDraftService::class)->createRoom($listing['property'], [
            'title' => 'Shared room',
            'room_number' => 'A1',
            'type' => 'shared',
            'gender_policy' => 'mixed',
            'sleeping_places_count' => 3,
            'room_rules_text' => 'Keep the room quiet at night.',
        ]);

        $places = app(HostSleepingPlaceDraftService::class)->autoCreatePlacesForRoom($room, 3);
        $place = $places->first();
        app(HostSleepingPlaceDraftService::class)->updateSleepingPlace($place, [
            'base_price_per_night' => 25,
            'currency' => 'EUR',
            'type' => 'single',
            'has_power_socket' => true,
            'has_lamp' => true,
        ]);

        $calendar = app(HostCalendarDraftService::class);
        $calendar->openDatesForPlace($listing['host'], $place, [
            'start' => now()->addDay()->toDateString(),
            'end' => now()->addDays(4)->toDateString(),
        ], [
            'price' => 25,
            'min_nights' => 2,
            'max_nights' => 14,
            'check_in_allowed' => true,
            'check_out_allowed' => true,
        ]);
        $calendar->setCleaningGap($listing['host'], $place, 0, 1);

        $this->assertSame(3, $room->sleepingPlaces()->count());
        $this->assertSame('1', $place->place_number);
        $this->assertGreaterThanOrEqual(3, $place->availabilityDays()->where('status', AvailabilityStatus::Available->value)->count());
        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'date' => now()->addDay()->toDateString(),
            'status' => AvailabilityStatus::Available->value,
            'price_override' => 25,
        ]);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $place->id,
            'date' => now()->addDay()->toDateString(),
            'status' => 'available',
            'price' => 25,
        ]);
        $this->assertSame(2, $place->fresh()->min_nights);
        $this->assertSame(14, $place->fresh()->max_nights);
        $this->assertSame(1, $place->fresh()->cleaning_gap_days);
    }

    public function test_readiness_detects_blockers_and_publish_sets_statuses_when_ready(): void
    {
        $incomplete = $this->listing(place: false);
        $readiness = app(HostListingReadinessService::class)->checkPublishReadiness($incomplete['property']);

        $this->assertFalse($readiness['ready']);
        $this->assertContains('missing_sleeping_places', collect($readiness['blocking'])->pluck('check_key')->all());

        $ready = $this->listing();
        $this->makeReady($ready);

        $readiness = app(HostListingReadinessService::class)->checkPublishReadiness($ready['property']);
        $published = app(HostListingPublishService::class)->publishIfReady($ready['host'], $ready['property']);

        $this->assertTrue($readiness['ready']);
        $this->assertSame('published', $published->publication_status);
        $this->assertSame('active', $published->status->value);
        $this->assertSame('published', $ready['room']->fresh()->publication_status);
        $this->assertSame('published', $ready['place']->fresh()->publication_status);
        $this->assertNotNull($published->published_at);
    }

    public function test_publish_blocks_another_host_and_incomplete_listing(): void
    {
        $listing = $this->listing(place: false);
        $otherHost = User::factory()->create(['is_host' => true]);

        $this->expectException(AuthorizationException::class);
        app(HostListingPublishService::class)->publishIfReady($otherHost, $listing['property']);
    }

    public function test_wizard_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing();
        app(HostListingWizardService::class)->resume($listing['host'], $listing['property']);

        Livewire::actingAs($listing['host'])
            ->test(CreateListingWizard::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_wizard.title'))
            ->assertSee(__('listing_wizard.steps.property'));

        $this->actingAs($listing['host'])
            ->get(route('host.listings.create', ['locale' => 'en', 'propertyId' => $listing['property']->id]))
            ->assertOk()
            ->assertSee(__('listing_wizard.title'));

        Livewire::actingAs($listing['host'])
            ->test(ListingWizardProgress::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_wizard.step_counter', ['current' => 1, 'total' => 5]));

        Livewire::actingAs($listing['host'])
            ->test(ListingReadinessChecklist::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_wizard.readiness.title'));

        Livewire::actingAs($listing['host'])
            ->test(BeforePublishChecklist::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_publish.checklist_title'));

        Livewire::actingAs($listing['host'])
            ->test(ListingDraftSaveIndicator::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_wizard.saved'));

        app()->setLocale('ru');

        Livewire::actingAs($listing['host'])
            ->test(CreateListingWizard::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_wizard.title', [], 'ru'));
    }

    /**
     * @return array{host: User, property: Property, room: Room|null, place: SleepingPlace|null}
     */
    private function listing(bool $room = true, bool $place = true): array
    {
        [$country, $region, $city] = $this->geo();
        $host = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'default_check_in_time' => '15:00',
            'default_check_out_time' => '11:00',
            'default_cancellation_policy' => 'flexible',
        ]);
        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $country->id,
                'region_id' => $region->id,
                'city_id' => $city->id,
                'status' => 'draft',
                'publication_status' => 'draft',
                'title' => 'Wizard property',
                'description' => 'A quiet shared stay.',
                'district' => 'Old Town',
                'rooms_count' => 1,
                'bathrooms_count' => 1,
                'showers_count' => 1,
                'kitchens_count' => 1,
                'rules' => ['kitchen', 'bathroom', 'quiet'],
                'emergency_contact_name' => 'Host',
                'emergency_contact_phone' => '+37060000000',
            ]);
        PropertyAccessDetail::factory()->for($property)->create([
            'key_pickup_method' => 'meet_host',
            'emergency_contact_available' => true,
        ]);
        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Wizard property',
            'summary' => 'A quiet shared stay.',
            'description' => 'A quiet shared stay.',
            'house_rules_text' => 'Kitchen and bathroom rules are clear.',
        ]);

        $createdRoom = null;
        $createdPlace = null;

        if ($room) {
            $createdRoom = Room::factory()->for($property)->create([
                'status' => 'draft',
                'publication_status' => 'draft',
                'title' => 'Room A',
                'room_rules_text' => 'Quiet after 22:00.',
                'sleeping_places_count' => 1,
            ]);
        }

        if ($place && $createdRoom instanceof Room) {
            $createdPlace = SleepingPlace::factory()->for($property)->for($createdRoom)->create([
                'status' => 'draft',
                'publication_status' => 'draft',
                'base_price_per_night' => 25,
                'deposit_amount' => 20,
                'cleaning_fee' => 5,
                'cancellation_policy' => 'flexible',
            ]);
        }

        return ['host' => $host, 'property' => $property, 'room' => $createdRoom, 'place' => $createdPlace];
    }

    /**
     * @param  array{host: User, property: Property, room: Room, place: SleepingPlace}  $listing
     */
    private function makeReady(array $listing): void
    {
        $this->media($listing['property'], $listing['host'], 'gallery');
        $this->media($listing['room'], $listing['host'], 'gallery');
        $this->media($listing['place'], $listing['host'], 'exact_place');

        foreach (range(1, 5) as $day) {
            AvailabilityDay::factory()->for($listing['place'])->create([
                'date' => now()->addDays($day)->toDateString(),
                'status' => AvailabilityStatus::Available,
            ]);
        }

        app(HostCalendarDraftService::class)->updateSettings($listing['host'], $listing['place'], [
            'default_price' => 25,
            'currency' => 'EUR',
            'check_in_time_from' => '15:00',
            'check_out_time_until' => '11:00',
        ]);
        app(HostCalendarDraftService::class)->openDatesForPlace($listing['host'], $listing['place'], [
            'start' => now()->addDay()->toDateString(),
            'end' => now()->addDays(6)->toDateString(),
        ], ['price' => 25]);
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
            ['name' => 'Vilnius County', 'source' => 'geonames', 'source_id' => '864389'],
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

    private function media(Model $model, User $host, string $collection): void
    {
        MediaItem::factory()->create([
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
