<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
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
        $this->assertTrue(Schema::hasColumn('property_translations', 'transport_description'));
        $this->assertTrue(Schema::hasIndex('properties', ['property_type', 'status']));
        $this->assertTrue(Schema::hasIndex('property_location_details', ['property_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['self_check_in_available']));

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
            'last_checked_at' => '2026-06-20 10:00:00',
        ]);
        PropertyAccessDetail::factory()->for($property)->create([
            'has_intercom' => true,
            'has_door_code' => true,
            'has_key_safe' => true,
            'key_safe_location_note' => 'Private key safe behind the blue door.',
            'self_check_in_available' => true,
            'access_24_7' => true,
            'delivery_allowed' => true,
        ]);

        return [$property->fresh(), $guest];
    }
}
