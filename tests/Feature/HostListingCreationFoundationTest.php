<?php

namespace Tests\Feature;

use App\Livewire\Host\Listings\ListingDraftAutosave;
use App\Livewire\Host\Listings\ListingReadinessPanel;
use App\Livewire\Host\Listings\ListingSuggestionsPanel;
use App\Livewire\Host\Listings\ListingWizardPage;
use App\Livewire\Host\Listings\ListingWizardProgress;
use App\Livewire\Host\Properties\PropertyAccessStep;
use App\Livewire\Host\Properties\PropertyAmenitiesStep;
use App\Livewire\Host\Properties\PropertyBasicStep;
use App\Livewire\Host\Properties\PropertyLocationStep;
use App\Livewire\Host\Properties\PropertyPhotosStep;
use App\Livewire\Host\Properties\PropertyRulesStep;
use App\Livewire\Host\Rooms\RoomBasicStep;
use App\Livewire\Host\Rooms\RoomComfortStep;
use App\Livewire\Host\Rooms\RoomPhotosStep;
use App\Livewire\Host\Rooms\RoomRulesStep;
use App\Livewire\Host\Rooms\RoomTemplatePicker;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceBasicStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceBatchCreateSheet;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceComfortStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlacePhotosStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlacePhysicalStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlacePositionStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceStorageStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceTemplatePicker;
use App\Models\City;
use App\Models\Country;
use App\Models\HostListingSuggestion;
use App\Models\ListingReadinessCheck;
use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\PropertyAddress;
use App\Models\PropertyAmenity;
use App\Models\PropertyPhoto;
use App\Models\PropertyRule;
use App\Models\Region;
use App\Models\Room;
use App\Models\RoomComfortDetail;
use App\Models\RoomPhoto;
use App\Models\RoomTemplate;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceComfortDetail;
use App\Models\SleepingPlacePhoto;
use App\Models\SleepingPlacePhysicalDetail;
use App\Models\SleepingPlacePositionDetail;
use App\Models\SleepingPlaceStorageDetail;
use App\Models\SleepingPlaceTemplate;
use App\Models\User;
use App\Services\HostListings\Creation\ListingDraftService;
use App\Services\HostListings\Creation\ListingPhotoService;
use App\Services\HostListings\Creation\ListingPublicationService;
use App\Services\HostListings\Creation\ListingReadinessService;
use App\Services\HostListings\Creation\ListingSuggestionService;
use App\Services\Properties\PropertyAccessService;
use App\Services\Properties\PropertyCreationService;
use App\Services\Rooms\RoomComfortService;
use App\Services\Rooms\RoomCreationService;
use App\Services\Rooms\RoomTemplateService;
use App\Services\SleepingPlaces\SleepingPlaceBatchCreationService;
use App\Services\SleepingPlaces\SleepingPlaceCreationService;
use App\Services\SleepingPlaces\SleepingPlaceDetailsService;
use App\Services\SleepingPlaces\SleepingPlaceTemplateService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class HostListingCreationFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_creation_schema_and_relationship_contract_exists(): void
    {
        foreach ([
            'property_addresses',
            'property_amenities',
            'property_rules',
            'property_access_details',
            'room_comfort_details',
            'sleeping_place_physical_details',
            'sleeping_place_comfort_details',
            'sleeping_place_storage_details',
            'sleeping_place_position_details',
            'property_photos',
            'room_photos',
            'sleeping_place_photos',
            'sleeping_place_creation_batches',
            'room_templates',
            'sleeping_place_templates',
            'listing_readiness_checks',
            'host_listing_suggestions',
            'listing_creation_drafts',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} table is missing.");
        }

        $this->assertTrue(Schema::hasColumns('property_addresses', [
            'property_id',
            'country_id',
            'city_id',
            'street_name',
            'house_number',
            'apartment_number',
            'latitude',
            'longitude',
            'approximate_latitude',
            'approximate_longitude',
            'show_exact_address_after_booking',
        ]));
        $this->assertTrue(Schema::hasIndex('property_addresses', ['country_id', 'city_id']));
        $this->assertTrue(Schema::hasIndex('property_addresses', ['approximate_latitude', 'approximate_longitude']));
        $this->assertTrue(Schema::hasIndex('property_amenities', ['property_id', 'amenity_key']));
        $this->assertTrue(Schema::hasIndex('property_rules', ['property_id', 'rule_key']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_creation_batches', ['room_id']));
        $this->assertTrue(Schema::hasIndex('listing_readiness_checks', ['sleeping_place_id']));
        $this->assertTrue(Schema::hasIndex('host_listing_suggestions', ['sleeping_place_id', 'status']));
        $this->assertTrue(Schema::hasIndex('listing_creation_drafts', ['user_id', 'draft_type']));

        $listing = $this->createReadyListing();

        $this->assertInstanceOf(PropertyAddress::class, $listing['property']->address);
        $this->assertInstanceOf(PropertyAccessDetail::class, $listing['property']->accessDetails);
        $this->assertTrue($listing['property']->amenityRecords->first() instanceof PropertyAmenity);
        $this->assertTrue($listing['property']->ruleRecords->first() instanceof PropertyRule);
        $this->assertTrue($listing['property']->photos->first() instanceof PropertyPhoto);
        $this->assertInstanceOf(RoomComfortDetail::class, $listing['room']->comfortDetails);
        $this->assertTrue($listing['room']->photos->first() instanceof RoomPhoto);
        $this->assertInstanceOf(SleepingPlacePhysicalDetail::class, $listing['place']->physicalDetails);
        $this->assertInstanceOf(SleepingPlaceComfortDetail::class, $listing['place']->comfortDetails);
        $this->assertInstanceOf(SleepingPlaceStorageDetail::class, $listing['place']->storageDetails);
        $this->assertInstanceOf(SleepingPlacePositionDetail::class, $listing['place']->positionDetails);
        $this->assertTrue($listing['place']->photos->first() instanceof SleepingPlacePhoto);
    }

    public function test_host_can_create_listing_structure_details_photos_batches_templates_and_drafts(): void
    {
        $host = User::factory()->host()->create();
        [$country, $region, $city] = $this->geo();
        $propertyService = app(PropertyCreationService::class);

        $property = $propertyService->create($host, [
            'title' => 'Vilnius shared apartment',
            'property_type' => 'apartment',
            'description' => 'A calm place with separate sleeping places.',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'city_id' => $city->id,
            'district_id' => null,
            'rooms_count' => 1,
            'max_residents_count' => 4,
        ]);
        $updated = $propertyService->update($host, $property, [
            'description' => 'Updated calm place with separate sleeping places.',
        ]);
        $address = $propertyService->saveAddress($host, $property, [
            'country_id' => $country->id,
            'city_id' => $city->id,
            'district_id' => null,
            'street_name' => 'Hidden Street',
            'house_number' => '12',
            'apartment_number' => '8',
            'latitude' => 54.700000,
            'longitude' => 25.270000,
            'approximate_latitude' => 54.701000,
            'approximate_longitude' => 25.271000,
            'public_location_label' => 'Old Town area',
            'show_exact_address_after_booking' => true,
            'show_street_before_booking' => false,
        ]);
        $publicAddress = app(PropertyAccessService::class)->buildAddressForGuest($property->fresh(), false);
        $confirmedAddress = app(PropertyAccessService::class)->buildAddressForGuest($property->fresh(), true);
        $amenities = $propertyService->saveAmenities($host, $property, [
            ['amenity_key' => 'wifi', 'available' => true, 'description' => 'Fast enough for calls.'],
            ['amenity_key' => 'kitchen', 'available' => true],
        ]);
        $rules = $propertyService->saveRules($host, $property, [
            ['rule_key' => 'smoking', 'allowed' => false, 'strict' => true],
            ['rule_key' => 'quiet_hours', 'allowed' => true, 'starts_at_time' => '22:00', 'ends_at_time' => '07:00'],
        ]);
        $access = $propertyService->saveAccessDetails($host, $property, [
            'entry_type' => 'shared_entrance',
            'has_intercom' => true,
            'has_key' => true,
            'self_check_in_available' => true,
            'key_pickup_instruction' => 'Meet the host near the entrance.',
            'check_in_instruction' => 'The exact access details open after booking.',
            'door_code_encrypted' => 'encrypted-door-code',
        ]);

        $room = app(RoomCreationService::class)->create($host, $property, [
            'title' => 'Room A',
            'room_type' => 'shared_room',
            'gender_policy' => 'mixed',
            'sleeping_places_count' => 2,
            'rules_text' => 'Keep the room quiet at night.',
        ]);
        $comfort = app(RoomComfortService::class)->save($host, $room, [
            'has_window' => true,
            'windows_count' => 1,
            'has_desk' => true,
            'has_air_conditioning' => false,
            'noise_level' => 'quiet',
            'can_close_window' => true,
        ]);

        $place = app(SleepingPlaceCreationService::class)->create($host, $room, [
            'title' => 'Lower bed 1',
            'place_number' => '1',
            'place_type' => 'bottom_bunk',
            'bed_type' => 'single',
            'base_price' => 20,
            'currency' => 'EUR',
            'max_guests_count' => 1,
        ]);
        $details = app(SleepingPlaceDetailsService::class);
        $physical = $details->savePhysical($host, $place, [
            'place_type' => 'bottom_bunk',
            'bed_type' => 'single',
            'length_cm' => 200,
            'width_cm' => 90,
            'has_mattress_protector' => true,
        ]);
        $placeComfort = $details->saveComfort($host, $place, [
            'has_pillow' => true,
            'has_blanket' => true,
            'has_bedding' => true,
            'has_socket' => true,
            'privacy_level' => 'medium',
        ]);
        $storage = $details->saveStorage($host, $place, [
            'has_locker' => true,
            'has_lockable_locker' => true,
            'locker_number' => 'L1',
            'can_store_valuables' => true,
        ]);
        $position = $details->savePosition($host, $place, [
            'near_window' => true,
            'bottom_bunk' => true,
            'near_passage' => false,
        ]);

        $photoService = app(ListingPhotoService::class);
        $propertyPhoto = $photoService->addPropertyPhoto($host, $property, ['path' => 'properties/1.jpg', 'is_main' => true]);
        $roomPhoto = $photoService->addRoomPhoto($host, $room, ['path' => 'rooms/1.jpg', 'is_main' => true]);
        $placePhoto = $photoService->addSleepingPlacePhoto($host, $place, ['path' => 'places/1.jpg', 'is_main' => true]);

        $batchPlaces = app(SleepingPlaceBatchCreationService::class)->createIdenticalBeds($host, $room, 2, [
            'title' => 'Batch bed',
            'place_type' => 'single_bed',
            'base_price' => 18,
            'currency' => 'EUR',
        ]);
        $bunks = app(SleepingPlaceBatchCreationService::class)->createBunkBeds($host, $room, 1);

        $roomTemplate = app(RoomTemplateService::class)->create($host, [
            'name' => 'Shared room template',
            'room_type' => 'shared_room',
            'template_json' => ['title' => 'Template room', 'gender_policy' => 'mixed'],
        ]);
        $templateRoom = app(RoomCreationService::class)->applyTemplate($host, $property, $roomTemplate);
        $placeTemplate = app(SleepingPlaceTemplateService::class)->create($host, [
            'name' => 'Single bed template',
            'place_type' => 'single_bed',
            'template_json' => ['title' => 'Template bed', 'base_price' => 19, 'currency' => 'EUR'],
        ]);
        $templatePlace = app(SleepingPlaceCreationService::class)->applyTemplate($host, $room, $placeTemplate);

        $draft = app(ListingDraftService::class)->createDraft($host, 'full_listing_wizard');
        $draft = app(ListingDraftService::class)->saveDraft($host, $draft, [
            'current_step' => 'sleeping_places',
            'draft_data_json' => ['property_id' => $property->id],
            'completed_steps_json' => ['property', 'rooms'],
        ]);
        $restored = app(ListingDraftService::class)->restoreDraft($host, $draft);

        $this->assertSame('Updated calm place with separate sleeping places.', $updated->description);
        $this->assertSame($property->id, $address->property_id);
        $this->assertArrayNotHasKey('house_number', $publicAddress);
        $this->assertArrayNotHasKey('apartment_number', $publicAddress);
        $this->assertSame('Old Town area', $publicAddress['public_location_label']);
        $this->assertSame('12', $confirmedAddress['house_number']);
        $this->assertCount(2, $amenities);
        $this->assertCount(2, $rules);
        $this->assertSame('encrypted-door-code', $access->door_code_encrypted);
        $this->assertSame($property->id, $room->property_id);
        $this->assertSame('quiet', $comfort->noise_level);
        $this->assertSame($room->id, $place->room_id);
        $this->assertSame($property->id, $place->property_id);
        $this->assertSame('bottom_bunk', $physical->place_type);
        $this->assertTrue($placeComfort->has_socket);
        $this->assertTrue($storage->can_store_valuables);
        $this->assertTrue($position->bottom_bunk);
        $this->assertSame('public', $propertyPhoto->visibility);
        $this->assertSame('public', $roomPhoto->visibility);
        $this->assertSame('public', $placePhoto->visibility);
        $this->assertCount(2, $batchPlaces);
        $this->assertCount(2, $bunks);
        $this->assertDatabaseHas('sleeping_place_creation_batches', ['room_id' => $room->id, 'status' => 'created']);
        $this->assertInstanceOf(RoomTemplate::class, $roomTemplate);
        $this->assertSame('Template room', $templateRoom->title);
        $this->assertInstanceOf(SleepingPlaceTemplate::class, $placeTemplate);
        $this->assertSame('Template bed', $templatePlace->title);
        $this->assertSame('sleeping_places', $draft->current_step);
        $this->assertSame($property->id, $restored['property_id']);
    }

    public function test_host_ownership_is_enforced_for_rooms_places_photos_and_publication(): void
    {
        $listing = $this->createReadyListing();
        $otherHost = User::factory()->host()->create();

        $this->expectException(AuthorizationException::class);

        app(RoomCreationService::class)->create($otherHost, $listing['property'], [
            'title' => 'Wrong room',
            'room_type' => 'shared_room',
        ]);
    }

    public function test_readiness_suggestions_and_publication_are_sleeping_place_first(): void
    {
        $listing = $this->createReadyListing(withSleepingPlacePhoto: false, withRules: false);
        $readiness = app(ListingReadinessService::class)->checkSleepingPlace($listing['place']);

        $this->assertTrue($readiness->contains(fn (ListingReadinessCheck $check) => $check->check_key === 'sleeping_place_photo' && $check->status === 'missing'));
        $this->assertTrue($readiness->contains(fn (ListingReadinessCheck $check) => $check->check_key === 'house_rules' && $check->status === 'missing'));
        $this->assertTrue($readiness->contains(fn (ListingReadinessCheck $check) => $check->check_key === 'sleeping_place_price' && $check->status === 'completed'));
        $this->assertFalse(app(ListingPublicationService::class)->canPublish($listing['place']));

        $suggestions = app(ListingSuggestionService::class)->generateForSleepingPlace($listing['place']);

        $this->assertTrue($suggestions->contains(fn (HostListingSuggestion $suggestion) => $suggestion->suggestion_key === 'add_sleeping_place_photo'));

        app(PropertyCreationService::class)->saveRules($listing['host'], $listing['property'], [
            ['rule_key' => 'smoking', 'allowed' => false],
            ['rule_key' => 'quiet_hours', 'allowed' => true],
        ]);
        app(ListingPhotoService::class)->addSleepingPlacePhoto($listing['host'], $listing['place'], [
            'path' => 'places/ready.jpg',
            'is_main' => true,
        ]);
        app(ListingPhotoService::class)->addPropertyPhoto($listing['host'], $listing['property'], [
            'path' => 'properties/ready.jpg',
            'is_main' => true,
        ]);
        app(ListingPhotoService::class)->addRoomPhoto($listing['host'], $listing['room'], [
            'path' => 'rooms/ready.jpg',
            'is_main' => true,
        ]);

        $published = app(ListingPublicationService::class)->publish($listing['host'], $listing['place']);

        $this->assertSame('published', $published->publication_status);
        $this->assertSame('published', $published->property->fresh()->publication_status);
        $this->assertSame('published', $published->room->fresh()->publication_status);
    }

    public function test_listing_wizard_livewire_components_render_translated_mobile_sections(): void
    {
        $listing = $this->createReadyListing();

        foreach ($this->componentClasses() as $componentClass) {
            $this->assertTrue(class_exists($componentClass), "{$componentClass} is missing.");
        }

        Livewire::actingAs($listing['host'])
            ->test(ListingWizardPage::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_wizard.title'))
            ->assertSee(__('listing_wizard.actions.save_draft'));

        Livewire::actingAs($listing['host'])
            ->test(ListingWizardProgress::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_wizard.progress.title'));

        Livewire::actingAs($listing['host'])
            ->test(ListingDraftAutosave::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_wizard.messages.autosaved'));

        Livewire::actingAs($listing['host'])
            ->test(ListingReadinessPanel::class, ['sleepingPlaceId' => $listing['place']->id])
            ->assertSee(__('listing_readiness.title'));

        Livewire::actingAs($listing['host'])
            ->test(ListingSuggestionsPanel::class, ['sleepingPlaceId' => $listing['place']->id])
            ->assertSee(__('listing_readiness.suggestions.title'));

        app()->setLocale('ru');

        Livewire::actingAs($listing['host'])
            ->test(ListingWizardPage::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_wizard.title', [], 'ru'));
    }

    public function test_translations_docs_and_forbidden_surfaces_are_clean(): void
    {
        foreach ([
            'listing_wizard',
            'property_amenities',
            'property_rules',
            'room_details',
            'sleeping_place_details',
            'listing_readiness',
        ] as $file) {
            $this->assertFileExists(lang_path("en/{$file}.php"));
            $this->assertFileExists(lang_path("ru/{$file}.php"));
        }

        foreach ([
            'docs/HOST_LISTING_CREATION.md',
            'docs/PROPERTY_CREATION.md',
            'docs/ROOM_CREATION.md',
            'docs/SLEEPING_PLACE_CREATION.md',
            'docs/LISTING_READINESS.md',
            'docs/LISTING_PHOTOS.md',
        ] as $doc) {
            $this->assertFileExists(base_path($doc));
        }

        $this->assertSame('Host Listing Creation', __('listing_wizard.title', [], 'en'));
        $this->assertSame('Создание объявления', __('listing_wizard.title', [], 'ru'));

        $this->assertFalse(is_dir(app_path('Filament')));
        $this->assertEmpty(glob(app_path('Livewire/**/*.volt.php')));
        $this->assertNotContains('admin', User::query()->pluck('role_mode')->all());
        $this->assertNotContains('support', User::query()->pluck('role_mode')->all());
        $this->assertNotContains('manager', User::query()->pluck('role_mode')->all());
        $this->assertNotContains('cleaner', User::query()->pluck('role_mode')->all());
    }

    /**
     * @return array{host: User, property: Property, room: Room, place: SleepingPlace}
     */
    private function createReadyListing(bool $withSleepingPlacePhoto = true, bool $withRules = true): array
    {
        $host = User::factory()->host()->create();
        [$country, $region, $city] = $this->geo();
        $property = Property::factory()->for($host, 'host')->create([
            'user_id' => $host->id,
            'host_user_id' => $host->id,
            'title' => 'Foundation property',
            'property_type' => 'apartment',
            'description' => 'A friendly shared property.',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'city_id' => $city->id,
            'publication_status' => 'draft',
        ]);
        $room = Room::factory()->for($property)->create([
            'user_id' => $host->id,
            'title' => 'Foundation room',
            'room_type' => 'shared',
            'rules_text' => 'Quiet after 22:00.',
            'publication_status' => 'draft',
        ]);
        $place = SleepingPlace::factory()->for($property)->for($room)->create([
            'user_id' => $host->id,
            'title' => 'Foundation place',
            'place_type' => 'single_bed',
            'base_price' => 22,
            'base_price_per_night' => 22,
            'currency' => 'EUR',
            'publication_status' => 'draft',
        ]);

        PropertyAddress::factory()->for($property)->create([
            'country_id' => $country->id,
            'city_id' => $city->id,
            'street_name' => 'Private Street',
            'house_number' => '7',
            'public_location_label' => 'Central area',
        ]);
        PropertyAccessDetail::factory()->for($property)->create([
            'entry_type' => 'shared_entrance',
            'check_in_instruction' => 'Access after booking.',
            'key_pickup_instruction' => 'Meet host.',
        ]);
        RoomComfortDetail::factory()->for($room)->create(['has_window' => true, 'has_desk' => true]);
        SleepingPlacePhysicalDetail::factory()->for($place)->create(['place_type' => 'single_bed']);
        SleepingPlaceComfortDetail::factory()->for($place)->create(['has_bedding' => true, 'has_socket' => true]);
        SleepingPlaceStorageDetail::factory()->for($place)->create(['has_locker' => true]);
        SleepingPlacePositionDetail::factory()->for($place)->create(['near_window' => true]);
        PropertyAmenity::factory()->for($property)->create(['amenity_key' => 'wifi', 'available' => true]);
        PropertyPhoto::factory()->for($property)->for($host, 'uploadedBy')->create(['path' => 'properties/foundation.jpg']);
        RoomPhoto::factory()->for($room)->for($host, 'uploadedBy')->create(['path' => 'rooms/foundation.jpg']);

        if ($withSleepingPlacePhoto) {
            SleepingPlacePhoto::factory()->for($place)->for($host, 'uploadedBy')->create(['path' => 'places/foundation.jpg']);
        }

        if ($withRules) {
            PropertyRule::factory()->for($property)->create(['rule_key' => 'smoking', 'allowed' => false]);
            PropertyRule::factory()->for($property)->create(['rule_key' => 'quiet_hours', 'allowed' => true]);
        }

        return [
            'host' => $host,
            'property' => $property->fresh(),
            'room' => $room->fresh(),
            'place' => $place->fresh(),
        ];
    }

    /**
     * @return array<class-string>
     */
    private function componentClasses(): array
    {
        return [
            ListingWizardPage::class,
            ListingWizardProgress::class,
            ListingDraftAutosave::class,
            ListingReadinessPanel::class,
            ListingSuggestionsPanel::class,
            PropertyBasicStep::class,
            PropertyLocationStep::class,
            PropertyAmenitiesStep::class,
            PropertyRulesStep::class,
            PropertyAccessStep::class,
            PropertyPhotosStep::class,
            RoomBasicStep::class,
            RoomComfortStep::class,
            RoomRulesStep::class,
            RoomPhotosStep::class,
            RoomTemplatePicker::class,
            SleepingPlaceBasicStep::class,
            SleepingPlacePhysicalStep::class,
            SleepingPlaceComfortStep::class,
            SleepingPlaceStorageStep::class,
            SleepingPlacePositionStep::class,
            SleepingPlacePhotosStep::class,
            SleepingPlaceBatchCreateSheet::class,
            SleepingPlaceTemplatePicker::class,
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
}
