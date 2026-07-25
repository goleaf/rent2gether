<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\GenderType;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Host\Properties\PropertyAccessStep;
use App\Livewire\Host\Properties\PropertyCompletionPanel;
use App\Livewire\Host\Properties\PropertyConditionStep;
use App\Livewire\Host\Properties\PropertyLocationStep;
use App\Livewire\Host\Properties\PropertyMainInfoStep;
use App\Livewire\Host\Properties\PropertyStructureStep;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\PropertyConditionDetail;
use App\Models\PropertyLocationDetail;
use App\Models\Region;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Properties\PropertyAccessService;
use App\Services\Properties\PropertyCompletionService;
use App\Services\Properties\PropertyGuestSummaryService;
use App\Services\Properties\PropertyPrivacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ExtendedPropertyFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_has_extended_detail_tables_relationships_indexes_and_cascade_delete(): void
    {
        $this->assertTrue(Schema::hasTable('property_location_details'));
        $this->assertTrue(Schema::hasTable('property_condition_details'));
        $this->assertTrue(Schema::hasTable('property_access_details'));
        $this->assertTrue(Schema::hasColumn('properties', 'property_subtype'));
        $this->assertTrue(Schema::hasColumn('properties', 'show_exact_address_after_confirmation'));
        $this->assertTrue(Schema::hasColumn('properties', 'free_sleeping_places_count'));
        $this->assertTrue(Schema::hasColumn('property_access_details', 'guest_rules_enabled'));
        $this->assertTrue(Schema::hasColumn('property_translations', 'transport_description'));
        $this->assertTrue(Schema::hasIndex('properties', ['property_type', 'status']));
        $this->assertTrue(Schema::hasIndex('property_location_details', ['property_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['self_check_in_available']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['guest_rules_enabled', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_location_details', ['district_safety_level', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_condition_details', ['cleanliness_level', 'property_id']));

        $property = Property::factory()->create();

        PropertyLocationDetail::factory()->for($property)->create([
            'nearest_metro' => 'Central Station',
            'transport_minutes_to_center' => 15,
            'district_safety_level' => 'good',
            'has_free_parking' => true,
        ]);
        PropertyConditionDetail::factory()->for($property)->create([
            'repair_state' => 'good',
            'cleanliness_level' => 'high',
            'has_mold' => false,
        ]);
        PropertyAccessDetail::factory()->for($property)->create([
            'has_intercom' => true,
            'has_key_safe' => true,
            'key_safe_location_note' => 'Private key safe behind the blue door.',
            'self_check_in_available' => true,
            'guest_rules_enabled' => true,
        ]);

        $property = $property->fresh(['locationDetails', 'conditionDetails', 'accessDetails']);

        $this->assertSame('Central Station', $property->locationDetails->nearest_metro);
        $this->assertSame('good', $property->conditionDetails->repair_state);
        $this->assertTrue($property->accessDetails->self_check_in_available);

        $property->forceDelete();

        $this->assertDatabaseMissing('property_location_details', ['property_id' => $property->id]);
        $this->assertDatabaseMissing('property_condition_details', ['property_id' => $property->id]);
        $this->assertDatabaseMissing('property_access_details', ['property_id' => $property->id]);
    }

    public function test_property_services_build_privacy_safe_guest_summary_and_confirmed_access(): void
    {
        [$property, $guest] = $this->propertyWithDetails();

        $privacy = app(PropertyPrivacyService::class);

        $this->assertFalse($privacy->canShowExactAddress($guest, $property, null));
        $this->assertFalse($privacy->canShowDoorCode($guest, $property, null));
        $this->assertFalse($privacy->canShowKeySafeLocation($guest, $property, null));

        $summary = app(PropertyGuestSummaryService::class)->build($property->fresh([
            'cityModel',
            'locationDetails',
            'conditionDetails',
            'accessDetails',
            'translations',
        ]), $guest);

        $this->assertSame(__('property.sections.main'), $summary['sections'][0]['title']);
        $this->assertStringContainsString('Vilnius', $summary['address']['public']);
        $this->assertStringNotContainsString('Secret Street', $summary['address']['public']);
        $this->assertStringNotContainsString('Private key safe', json_encode($summary, JSON_THROW_ON_ERROR));
        $this->assertStringContainsString('15', json_encode($summary, JSON_THROW_ON_ERROR));
        $this->assertStringContainsString(__('property.fields.guest_rules_enabled'), json_encode($summary, JSON_THROW_ON_ERROR));
        $this->assertStringContainsString(__('property.fields.floor_condition'), json_encode($summary, JSON_THROW_ON_ERROR));

        $booking = Booking::factory()->create([
            'guest_user_id' => $guest->id,
            'host_user_id' => $property->host_user_id,
            'property_id' => $property->id,
            'status' => BookingStatus::Confirmed->value,
            'payment_status' => PaymentStatus::Paid->value,
        ]);

        $this->assertTrue($privacy->canShowExactAddress($guest, $property, $booking));
        $this->assertTrue($privacy->canShowKeySafeLocation($guest, $property, $booking));

        $instructions = app(PropertyAccessService::class)->getConfirmedBookingAccessInstructions($property, $booking);

        $this->assertStringContainsString('Private key safe behind the blue door.', $instructions);
    }

    public function test_host_extended_property_steps_update_data_and_block_other_hosts(): void
    {
        [$property] = $this->propertyWithDetails();
        $host = $property->host;
        $otherHost = User::factory()->create(['is_host' => true]);

        Livewire::actingAs($host)
            ->test(PropertyMainInfoStep::class, ['property' => $property])
            ->set('translations.en.title', 'Quiet shared flat')
            ->set('translations.ru.title', 'Тихая общая квартира')
            ->set('translations.en.short_description', 'A calm shared flat near daily transport.')
            ->set('translations.ru.short_description', 'Спокойная общая квартира рядом с транспортом.')
            ->set('translations.en.full_description', 'Guests share the property, kitchen, bathroom, and common entrance.')
            ->set('translations.ru.full_description', 'Гости пользуются общим помещением, кухней, ванной и входом.')
            ->set('propertyType', PropertyType::Apartment->value)
            ->set('propertySubtype', 'shared_flat')
            ->set('district', 'Old Town')
            ->set('street', 'Secret Street')
            ->set('houseNumber', '10')
            ->set('apartmentNumber', '8')
            ->set('showExactAddressBeforeBooking', false)
            ->set('showExactAddressAfterConfirmation', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee(__('property.messages.saved'));

        Livewire::actingAs($host)
            ->test(PropertyStructureStep::class, ['property' => $property])
            ->set('livingArea', 42.5)
            ->set('bedroomsCount', 2)
            ->set('sharedRoomsCount', 1)
            ->set('maxResidents', 8)
            ->set('currentResidentsCount', 4)
            ->set('freeSleepingPlacesCount', 3)
            ->set('occupiedSleepingPlacesCount', 5)
            ->set('canBookSleepingPlace', true)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(PropertyLocationStep::class, ['property' => $property])
            ->set('nearestMetro', 'Central Station')
            ->set('transportMinutesToCenter', 15)
            ->set('districtNoiseLevel', 'quiet')
            ->set('districtSafetyLevel', 'good')
            ->set('hasFreeParking', true)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(PropertyConditionStep::class, ['property' => $property])
            ->set('repairState', 'good')
            ->set('cleanlinessLevel', 'high')
            ->set('smellLevel', 'none')
            ->set('humidityLevel', 'normal')
            ->set('winterTemperatureLevel', 'warm')
            ->set('summerTemperatureLevel', 'normal')
            ->set('hasHeating', true)
            ->set('hasHotWater', true)
            ->set('indoorNoiseLevel', 'quiet')
            ->set('lightLevel', 'bright')
            ->set('furnitureCondition', 'good')
            ->set('kitchenCondition', 'good')
            ->set('hasMold', false)
            ->set('lastCleanedAt', '2026-06-19')
            ->set('lastRepairedAt', '2026-05-20')
            ->set('lastCheckedAt', '2026-06-20')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(PropertyAccessStep::class, ['property' => $property])
            ->set('hasIntercom', true)
            ->set('hasDoorCode', true)
            ->set('hasKeySafe', true)
            ->set('meetHostRequired', true)
            ->set('meetHostRepresentativeRequired', true)
            ->set('selfCheckInAvailable', true)
            ->set('access247', true)
            ->set('guestRulesEnabled', true)
            ->set('courierRulesEnabled', true)
            ->set('deliveryAllowed', true)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(PropertyCompletionPanel::class, ['property' => $property->fresh()])
            ->assertSee(__('property.completion.title'));

        $this->assertGreaterThan(0, app(PropertyCompletionService::class)->percentage($property->fresh([
            'translations',
            'locationDetails',
            'conditionDetails',
            'accessDetails',
        ])));

        Livewire::actingAs($otherHost)
            ->test(PropertyLocationStep::class, ['property' => $property])
            ->assertForbidden();

        $this->assertDatabaseHas('property_access_details', [
            'property_id' => $property->id,
            'meet_host_required' => true,
            'meet_host_representative_required' => true,
            'guest_rules_enabled' => true,
            'courier_rules_enabled' => true,
        ]);
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'current_residents_count' => 4,
            'current_guests_count' => 4,
            'free_sleeping_places_count' => 3,
            'free_places_count' => 3,
            'occupied_sleeping_places_count' => 5,
            'occupied_places_count' => 5,
        ]);
        $this->assertDatabaseHas('property_translations', [
            'property_id' => $property->id,
            'locale' => 'en',
            'short_description' => 'A calm shared flat near daily transport.',
        ]);
        $this->assertDatabaseHas('property_condition_details', [
            'property_id' => $property->id,
            'smell_level' => 'none',
            'humidity_level' => 'normal',
            'indoor_noise_level' => 'quiet',
        ]);
        $this->assertDatabaseMissing('property_access_details', [
            'property_id' => $property->id,
            'key_pickup_contact_type' => 'manager',
        ]);
    }

    public function test_listing_detail_renders_public_property_sections_without_private_access_data(): void
    {
        [$property] = $this->propertyWithDetails();
        $room = Room::factory()->for($property)->create(['status' => RoomStatus::Active]);
        $place = SleepingPlace::factory()->for($room)->for($property)->create(['status' => SleepingPlaceStatus::Active]);

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee(__('property.public.title'))
            ->assertSee(__('property.sections.location'))
            ->assertSee(__('property.sections.access'))
            ->assertSee('Central Station')
            ->assertSee(__('property.values.self_check_in'))
            ->assertDontSee('Secret Street 10')
            ->assertDontSee('Private key safe behind the blue door.');
    }

    public function test_search_filters_by_extended_location_condition_and_access_details(): void
    {
        $country = Country::factory()->create(['name_en' => 'Lithuania', 'name' => 'Lithuania']);
        $region = Region::factory()->for($country)->create(['name' => 'Vilnius County']);
        $city = City::factory()->for($country)->for($region)->create(['name' => 'Extended Search City']);

        $matched = $this->searchablePlace($city, 'Matched Extended Property Place');
        PropertyLocationDetail::factory()->for($matched->property)->create([
            'nearest_metro' => 'Central Metro',
            'nearest_metro_distance_meters' => 600,
            'nearest_bus_stop' => 'Main Bus',
            'nearest_bus_stop_distance_meters' => 300,
            'nearest_shop' => 'Corner Shop',
            'nearest_pharmacy' => 'Daily Pharmacy',
            'nearest_hospital' => 'City Hospital',
            'nearest_university' => 'Central University',
            'nearest_railway_station' => 'Central Railway',
            'railway_station_distance_meters' => 1200,
            'nearest_airport' => 'City Airport',
            'airport_distance_meters' => 12000,
            'distance_to_center_meters' => 1200,
            'walk_minutes_to_center' => 20,
            'transport_minutes_to_center' => 10,
            'transport_convenience_level' => 'good',
            'district_noise_level' => 'quiet',
            'district_safety_level' => 'good',
            'street_lighting_level' => 'good',
            'has_free_parking' => true,
            'has_paid_parking' => true,
        ]);
        PropertyConditionDetail::factory()->for($matched->property)->create([
            'cleanliness_level' => 'high',
            'has_insects' => false,
            'has_mold' => false,
            'humidity_level' => 'normal',
            'winter_temperature_level' => 'warm',
            'summer_temperature_level' => 'normal',
            'indoor_noise_level' => 'quiet',
            'light_level' => 'bright',
        ]);
        PropertyAccessDetail::factory()->for($matched->property)->create([
            'entrance_type' => 'code_door',
            'has_door_code' => true,
            'has_electronic_lock' => true,
            'has_key_safe' => true,
            'access_24_7' => true,
            'has_night_entry_restrictions' => false,
            'guest_rules_enabled' => true,
            'courier_rules_enabled' => true,
            'delivery_allowed' => true,
            'self_check_in_available' => true,
        ]);

        $filtered = $this->searchablePlace($city, 'Filtered Extended Property Place', [
            'distance_to_center_meters' => 9000,
            'has_parking' => false,
        ]);
        PropertyLocationDetail::factory()->for($filtered->property)->create([
            'nearest_metro' => null,
            'nearest_metro_distance_meters' => 2500,
            'nearest_bus_stop' => null,
            'nearest_bus_stop_distance_meters' => 1500,
            'nearest_shop' => null,
            'nearest_supermarket' => null,
            'nearest_pharmacy' => null,
            'nearest_hospital' => null,
            'nearest_clinic' => null,
            'nearest_university' => null,
            'nearest_railway_station' => null,
            'nearest_train_station' => null,
            'railway_station_distance_meters' => 6000,
            'nearest_airport' => null,
            'airport_distance_meters' => 25000,
            'distance_to_center_meters' => 9000,
            'walk_minutes_to_center' => 70,
            'transport_minutes_to_center' => 45,
            'transport_convenience_level' => 'low',
            'district_noise_level' => 'loud',
            'district_safety_level' => 'low',
            'street_lighting_level' => 'low',
            'has_free_parking' => false,
            'has_paid_parking' => false,
        ]);
        PropertyConditionDetail::factory()->for($filtered->property)->create([
            'cleanliness_level' => 'low',
            'has_insects' => true,
            'has_mold' => true,
            'humidity_level' => 'damp',
            'winter_temperature_level' => 'cold',
            'summer_temperature_level' => 'hot',
            'indoor_noise_level' => 'loud',
            'light_level' => 'low',
        ]);
        PropertyAccessDetail::factory()->for($filtered->property)->create([
            'entrance_type' => 'shared_entrance',
            'has_door_code' => false,
            'has_electronic_lock' => false,
            'has_key_safe' => false,
            'access_24_7' => false,
            'has_night_entry_restrictions' => true,
            'guest_rules_enabled' => false,
            'courier_rules_enabled' => false,
            'delivery_allowed' => false,
            'self_check_in_available' => false,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'near_center' => true,
            'near_metro' => true,
            'near_bus' => true,
            'near_shop' => true,
            'near_pharmacy' => true,
            'near_hospital' => true,
            'near_university' => true,
            'near_railway' => true,
            'near_airport' => true,
            'transport' => true,
            'quiet_district' => true,
            'safe_district' => true,
            'street_light' => true,
            'free_parking' => true,
            'paid_parking' => true,
            'clean_property' => true,
            'no_insects' => true,
            'no_mold' => true,
            'normal_humidity' => true,
            'warm_winter' => true,
            'cool_summer' => true,
            'quiet_property' => true,
            'bright_property' => true,
            'door_code' => true,
            'electronic_lock' => true,
            'key_safe' => true,
            'access_24_7' => true,
            'no_night_restrictions' => true,
            'guest_rules' => true,
            'courier_rules' => true,
            'delivery' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Matched Extended Property Place');
        $response->assertDontSee('Filtered Extended Property Place');
    }

    /**
     * @return array{0: Property, 1: User}
     */
    private function propertyWithDetails(): array
    {
        $country = Country::factory()->create(['name_en' => 'Lithuania', 'name' => 'Lithuania']);
        $region = Region::factory()->for($country)->create(['name' => 'Vilnius County']);
        $city = City::factory()->for($country)->for($region)->create(['name' => 'Vilnius']);
        $host = User::factory()->create(['is_host' => true]);
        $guest = User::factory()->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->for($country, 'countryModel')
            ->for($region)
            ->for($city, 'cityModel')
            ->create([
                'user_id' => $host->id,
                'host_user_id' => $host->id,
                'country_id' => $country->id,
                'region_id' => $region->id,
                'city_id' => $city->id,
                'city' => 'Vilnius',
                'district' => 'Old Town',
                'street' => 'Secret Street',
                'house_number' => '10',
                'apartment_number' => '8',
                'floor' => 2,
                'total_floors' => 5,
                'has_elevator' => false,
                'total_area' => 64.5,
                'living_area' => 42.5,
                'rooms_count' => 3,
                'bedrooms_count' => 2,
                'shared_rooms_count' => 1,
                'bathrooms_count' => 1,
                'showers_count' => 1,
                'kitchens_count' => 1,
                'balconies_count' => 1,
                'max_residents' => 8,
                'current_residents_count' => 4,
                'free_sleeping_places_count' => 2,
                'occupied_sleeping_places_count' => 2,
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
                'show_exact_address_before_booking' => false,
                'show_exact_address_after_payment' => true,
            ]);

        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet shared flat',
            'summary' => 'Simple shared flat near the center.',
            'location_description' => 'The area is easy to understand before arrival.',
            'transport_description' => 'Metro and bus are close.',
            'condition_description' => 'Clean and checked by the host.',
            'access_description' => 'Self check-in is available after confirmation.',
        ]);
        $property->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихая общая квартира',
            'summary' => 'Простая общая квартира рядом с центром.',
        ]);

        PropertyLocationDetail::factory()->for($property)->create([
            'nearest_metro' => 'Central Station',
            'transport_minutes_to_center' => 15,
            'district_safety_level' => 'good',
            'district_noise_level' => 'quiet',
            'has_parking_nearby' => true,
            'has_free_parking' => true,
        ]);
        PropertyConditionDetail::factory()->for($property)->create([
            'repair_state' => 'good',
            'cleanliness_level' => 'high',
            'has_mold' => false,
            'has_insects' => false,
            'floor_condition' => 'good',
            'walls_condition' => 'good',
            'last_cleaned_at' => '2026-06-19 10:00:00',
            'last_repaired_at' => '2026-05-20 10:00:00',
            'last_checked_at' => '2026-06-20 10:00:00',
        ]);
        PropertyAccessDetail::factory()->for($property)->create([
            'has_intercom' => true,
            'has_door_code' => true,
            'has_key_safe' => true,
            'key_safe_location_note' => 'Private key safe behind the blue door.',
            'self_check_in_available' => true,
            'access_24_7' => true,
            'guest_rules_enabled' => true,
            'courier_rules_enabled' => true,
            'delivery_allowed' => true,
            'delivery_dropoff_location' => 'Building entrance',
        ]);

        return [$property->fresh(), $guest];
    }

    /**
     * @param  array<string, mixed>  $propertyAttributes
     */
    private function searchablePlace(City $city, string $title, array $propertyAttributes = []): SleepingPlace
    {
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create([
                ...[
                    'host_user_id' => $host->id,
                    'user_id' => $host->id,
                    'country_id' => $city->country_id,
                    'region_id' => $city->region_id,
                    'city_id' => $city->id,
                    'city' => $city->name,
                    'district' => 'Central',
                    'status' => PropertyStatus::Active,
                    'type' => PropertyType::Apartment,
                ],
                ...$propertyAttributes,
            ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
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
                'base_price' => 30,
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
