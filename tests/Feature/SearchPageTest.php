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
use App\Livewire\Search\SleepingPlaceSearch;
use App\Models\Amenity;
use App\Models\AvailabilityDay;
use App\Models\Bed;
use App\Models\City;
use App\Models\ComplaintCase;
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\PropertyAddress;
use App\Models\PropertyConditionDetail;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\PropertyLocationDetail;
use App\Models\PropertyRatingSnapshot;
use App\Models\Room;
use App\Models\RoomCompatibilityProfile;
use App\Models\RoomCurrentOccupancySnapshot;
use App\Models\RoomOccupantSnapshot;
use App\Models\RoomRatingSnapshot;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceRatingSnapshot;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-19 12:00:00');
        CarbonImmutable::setTestNow('2026-06-19 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_search_page_loads(): void
    {
        $response = $this->get(route('search.index', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSeeLivewire(SleepingPlaceSearch::class);
    }

    public function test_search_page_shows_active_sleeping_places(): void
    {
        $city = $this->city('Berlin');
        $this->createSearchPlace('Test Sleeping Place Alpha', $city);

        $response = $this->get(route('search.index', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSee('Test Sleeping Place Alpha');
    }

    public function test_search_filters_by_city(): void
    {
        $berlin = $this->city('Berlin');
        $paris = $this->city('Paris');
        $this->createSearchPlace('Berlin Sleeping Place', $berlin);
        $this->createSearchPlace('Paris Sleeping Place', $paris);

        $response = $this->get(route('search.index', ['locale' => 'en', 'city' => $berlin->id]));

        $response->assertOk();
        $response->assertSee('Berlin Sleeping Place');
        $response->assertDontSee('Paris Sleeping Place');
    }

    public function test_search_filters_by_date_availability(): void
    {
        $city = $this->city('Vilnius');
        $open = $this->createSearchPlace('Open Sleeping Place', $city);
        $blocked = $this->createSearchPlace('Blocked Sleeping Place', $city);
        AvailabilityDay::factory()->for($blocked)->create([
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'in' => '2026-07-10',
            'out' => '2026-07-12',
        ]));

        $response->assertOk();
        $response->assertSee('Open Sleeping Place');
        $response->assertDontSee('Blocked Sleeping Place');
        $this->assertTrue($open->exists);
    }

    public function test_search_filters_by_price(): void
    {
        $city = $this->city('Riga');
        $this->createSearchPlace('Budget Place', $city, ['base_price_per_night' => 25]);
        $this->createSearchPlace('Premium Place', $city, ['base_price_per_night' => 80]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'price_max' => 30,
        ]));

        $response->assertOk();
        $response->assertSee('Budget Place');
        $response->assertDontSee('Premium Place');
    }

    public function test_search_filters_by_amenity(): void
    {
        $city = $this->city('Tallinn');
        $wifi = $this->amenity('wifi', 'Wi-Fi');
        $withWifi = $this->createSearchPlace('Wi-Fi Place', $city);
        $withoutWifi = $this->createSearchPlace('Simple Place', $city);
        $withWifi->property->amenities()->attach($wifi);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'wifi' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Wi-Fi Place');
        $response->assertDontSee('Simple Place');
        $this->assertTrue($withoutWifi->exists);
    }

    public function test_search_filters_by_room_gender_policy(): void
    {
        $city = $this->city('Kaunas');
        $this->createSearchPlace('Female Room Place', $city, [], ['type' => PropertyType::Apartment], [
            'gender_policy' => GenderType::Female,
        ]);
        $this->createSearchPlace('Mixed Room Place', $city, [], ['type' => PropertyType::Apartment], [
            'gender_policy' => GenderType::Mixed,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'gender' => GenderType::Female->value,
        ]));

        $response->assertOk();
        $response->assertSee('Female Room Place');
        $response->assertDontSee('Mixed Room Place');
    }

    public function test_search_filters_by_premise_criteria(): void
    {
        $city = $this->city('Premise City');

        $this->createSearchPlace('Matched Premise Place', $city, [], [
            'property_type' => PropertyType::Apartment->value,
            'type' => PropertyType::Apartment->value,
            'repair_state' => 'new',
            'entrance_type' => 'private_entrance',
            'floor' => 1,
            'floors_count' => 5,
            'has_elevator' => true,
            'balconies_count' => 1,
        ], [
            'has_balcony' => true,
            'window_view' => 'courtyard',
            'noise_level' => 'quiet',
        ]);

        $this->createSearchPlace('Filtered Premise Place', $city, [], [
            'property_type' => PropertyType::Apartment->value,
            'type' => PropertyType::Apartment->value,
            'repair_state' => 'old',
            'entrance_type' => 'shared_entrance',
            'floor' => 3,
            'floors_count' => 3,
            'has_elevator' => true,
            'balconies_count' => 0,
        ], [
            'has_balcony' => false,
            'window_view' => 'street',
            'noise_level' => 'moderate',
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'property_type' => PropertyType::Apartment->value,
            'new_home' => true,
            'private_entrance' => true,
            'first_floor' => true,
            'balcony' => true,
            'quiet_windows' => true,
            'courtyard_windows' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Matched Premise Place');
        $response->assertDontSee('Filtered Premise Place');
    }

    public function test_premise_criteria_search_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasIndex('properties', ['status', 'repair_state']));
        $this->assertTrue(Schema::hasIndex('properties', ['status', 'floor']));
        $this->assertTrue(Schema::hasIndex('properties', ['status', 'floors_count']));
        $this->assertTrue(Schema::hasIndex('properties', ['status', 'balconies_count']));
        $this->assertTrue(Schema::hasIndex('property_condition_details', ['repair_state', 'property_id']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_balcony']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'window_view']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'noise_level']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['entrance_type', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['has_private_entrance', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['has_shared_entrance', 'property_id']));
    }

    public function test_search_filters_by_extended_location_condition_and_access_criteria(): void
    {
        $city = $this->city('Extended Criteria City');

        $matched = $this->createSearchPlace('Matched Extended Property Place', $city, [], [
            'distance_to_center_meters' => 1200,
            'has_parking' => true,
        ]);
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

        $filtered = $this->createSearchPlace('Filtered Extended Property Place', $city, [], [
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

    public function test_extended_property_search_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('property_access_details', 'guest_rules_enabled'));
        $this->assertTrue(Schema::hasIndex('property_location_details', ['nearest_metro', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_location_details', ['distance_to_center_meters', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_location_details', ['transport_convenience_level', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_location_details', ['district_safety_level', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_location_details', ['has_free_parking', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_condition_details', ['cleanliness_level', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_condition_details', ['has_insects', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_condition_details', ['has_mold', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['guest_rules_enabled', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['delivery_allowed', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['access_24_7', 'property_id']));
    }

    public function test_search_filters_by_private_room_criteria(): void
    {
        $city = $this->city('Private Room City');

        $this->createSearchPlace('Matched Private Room Place', $city, [], [], [
            'type' => RoomType::Private->value,
            'room_type' => RoomType::Private->value,
            'is_private' => true,
            'is_shared' => false,
            'gender_policy' => GenderType::Female->value,
            'gender_type' => GenderType::Female->value,
            'living_format' => 'student',
            'is_for_one_person' => true,
            'max_guests' => 1,
            'capacity' => 1,
            'has_window' => true,
            'windows_count' => 1,
            'has_lock' => true,
            'has_lockable_door' => true,
            'has_room_key' => true,
            'has_air_conditioning' => true,
            'has_ac' => true,
            'has_heating' => true,
            'has_desk' => true,
            'has_wardrobe' => true,
            'has_lockers' => true,
            'has_balcony' => true,
            'noise_level' => 'quiet',
            'light_level' => 'bright',
            'is_pass_through' => false,
        ]);

        $this->createSearchPlace('Filtered Private Room Place', $city, [], [], [
            'type' => RoomType::Shared->value,
            'room_type' => RoomType::Shared->value,
            'is_private' => false,
            'is_shared' => true,
            'gender_policy' => GenderType::Male->value,
            'gender_type' => GenderType::Male->value,
            'living_format' => 'worker',
            'is_for_one_person' => false,
            'max_guests' => 6,
            'capacity' => 6,
            'has_window' => false,
            'windows_count' => 0,
            'has_lock' => false,
            'has_lockable_door' => false,
            'has_room_key' => false,
            'has_air_conditioning' => false,
            'has_ac' => false,
            'has_heating' => false,
            'has_desk' => false,
            'has_wardrobe' => false,
            'has_lockers' => false,
            'has_balcony' => false,
            'noise_level' => 'moderate',
            'light_level' => 'moderate',
            'is_pass_through' => true,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'private_room' => true,
            'female_room' => true,
            'student_room' => true,
            'one_guest_room' => true,
            'room_window' => true,
            'room_lock' => true,
            'room_ac' => true,
            'room_heating' => true,
            'room_desk' => true,
            'room_wardrobe' => true,
            'room_locker' => true,
            'room_balcony' => true,
            'quiet_room' => true,
            'bright_room' => true,
            'non_pass_through' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Matched Private Room Place');
        $response->assertDontSee('Filtered Private Room Place');
    }

    public function test_search_filters_by_shared_large_worker_room_criteria(): void
    {
        $city = $this->city('Shared Worker City');

        $this->createSearchPlace('Matched Shared Worker Room', $city, [], [], [
            'type' => RoomType::Shared->value,
            'room_type' => RoomType::Shared->value,
            'is_private' => false,
            'is_shared' => true,
            'gender_policy' => GenderType::Male->value,
            'gender_type' => GenderType::Male->value,
            'living_format' => 'worker',
            'max_guests' => 8,
            'capacity' => 8,
            'is_pass_through' => true,
        ]);

        $this->createSearchPlace('Filtered Shared Worker Room', $city, [], [], [
            'type' => RoomType::Shared->value,
            'room_type' => RoomType::Shared->value,
            'is_private' => false,
            'is_shared' => true,
            'gender_policy' => GenderType::Male->value,
            'gender_type' => GenderType::Male->value,
            'living_format' => 'worker',
            'max_guests' => 4,
            'capacity' => 4,
            'is_pass_through' => true,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'shared_room' => true,
            'male_room' => true,
            'worker_room' => true,
            'room_over_6' => true,
            'pass_through' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Matched Shared Worker Room');
        $response->assertDontSee('Filtered Shared Worker Room');
    }

    public function test_search_filters_by_mixed_tourist_long_stay_room_criteria(): void
    {
        $city = $this->city('Tourist Room City');

        $this->createSearchPlace('Matched Tourist Room Place', $city, [], [], [
            'gender_policy' => GenderType::Mixed->value,
            'gender_type' => GenderType::Mixed->value,
            'living_format' => 'tourist',
            'is_for_long_stay' => true,
            'max_guests' => 4,
            'capacity' => 4,
            'has_window' => false,
            'windows_count' => 0,
            'has_lock' => false,
            'has_lockable_door' => false,
            'has_room_key' => false,
            'is_pass_through' => true,
        ]);

        $this->createSearchPlace('Filtered Tourist Room Place', $city, [], [], [
            'gender_policy' => GenderType::Mixed->value,
            'gender_type' => GenderType::Mixed->value,
            'living_format' => 'tourist',
            'is_for_long_stay' => true,
            'max_guests' => 5,
            'capacity' => 5,
            'has_window' => true,
            'windows_count' => 1,
            'has_lock' => false,
            'has_lockable_door' => false,
            'has_room_key' => false,
            'is_pass_through' => true,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'mixed_room' => true,
            'tourist_room' => true,
            'long_stay_room' => true,
            'room_up_to_4' => true,
            'room_no_window' => true,
            'room_no_lock' => true,
            'pass_through' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Matched Tourist Room Place');
        $response->assertDontSee('Filtered Tourist Room Place');
    }

    public function test_room_criteria_search_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'type']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'room_type']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'gender_policy']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'gender_type']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'is_private']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'is_shared']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'living_format']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'is_for_one_person']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'is_for_long_stay']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'capacity']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'max_guests']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_window']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'windows_count']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_lock']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_lockable_door']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_room_key']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_air_conditioning']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_ac']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_heating']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_desk']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_wardrobe']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_lockers']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'has_balcony']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'noise_level']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'is_pass_through']));
        $this->assertTrue(Schema::hasIndex('rooms', ['status', 'light_level']));
    }

    public function test_search_filters_by_allowed_rule_criteria_across_place_room_and_property(): void
    {
        $city = $this->city('Allowed Rules City');
        $matched = $this->createSearchPlace('Matched Allowed Rules Place', $city);
        $this->createSearchPlace('Filtered Allowed Rules Place', $city);

        $rules = $this->rules([
            'smoking_allowed',
            'pets_allowed',
            'visitors_allowed',
            'couples_allowed',
            'children_allowed',
            'cooking_allowed',
            'night_cooking_allowed',
            'washing_machine_at_night_allowed',
            'night_work_allowed',
            'late_entry_allowed',
        ]);

        $matched->property->rules()->attach([
            $rules['smoking_allowed']->id,
            $rules['pets_allowed']->id,
            $rules['visitors_allowed']->id,
            $rules['couples_allowed']->id,
        ]);
        $matched->room->rules()->attach([
            $rules['children_allowed']->id,
            $rules['cooking_allowed']->id,
            $rules['night_cooking_allowed']->id,
            $rules['washing_machine_at_night_allowed']->id,
        ]);
        $matched->rules()->attach([
            $rules['night_work_allowed']->id,
            $rules['late_entry_allowed']->id,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'smoking' => true,
            'pets' => true,
            'visitors' => true,
            'couples' => true,
            'children' => true,
            'cook' => true,
            'cook_night' => true,
            'wash_night' => true,
            'work_night' => true,
            'late_return' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Matched Allowed Rules Place');
        $response->assertDontSee('Filtered Allowed Rules Place');
    }

    public function test_search_filters_by_restrictive_rule_criteria_across_place_room_and_property(): void
    {
        $city = $this->city('Restrictive Rules City');
        $matched = $this->createSearchPlace('Matched Restrictive Rules Place', $city);
        $this->createSearchPlace('Filtered Restrictive Rules Place', $city);

        $rules = $this->rules([
            'no_smoking',
            'no_pets',
            'no_visitors',
            'adults_only',
            'no_noise_after_time',
            'quiet_hours_after_22',
            'no_washing_machine_at_night',
            'no_main_light_at_night',
            'entry_time_limit',
            'cleaning_rules',
            'cleaning_schedule',
            'remove_shoes_inside',
            'no_alcohol',
            'no_parties',
            'no_unregistered_people',
            'no_loud_music',
            'no_food_storage_in_room',
            'no_eating_on_bed',
            'no_sleeping_place_changes_without_permission',
            'do_not_occupy_other_shelves',
            'do_not_use_other_residents_things',
        ]);

        $matched->property->rules()->attach([
            $rules['no_smoking']->id,
            $rules['no_pets']->id,
            $rules['no_visitors']->id,
            $rules['adults_only']->id,
            $rules['no_noise_after_time']->id,
            $rules['quiet_hours_after_22']->id,
            $rules['no_washing_machine_at_night']->id,
        ]);
        $matched->room->rules()->attach([
            $rules['no_main_light_at_night']->id,
            $rules['entry_time_limit']->id,
            $rules['cleaning_rules']->id,
            $rules['cleaning_schedule']->id,
            $rules['remove_shoes_inside']->id,
            $rules['no_alcohol']->id,
            $rules['no_parties']->id,
        ]);
        $matched->rules()->attach([
            $rules['no_unregistered_people']->id,
            $rules['no_loud_music']->id,
            $rules['no_food_storage_in_room']->id,
            $rules['no_eating_on_bed']->id,
            $rules['no_sleeping_place_changes_without_permission']->id,
            $rules['do_not_occupy_other_shelves']->id,
            $rules['do_not_use_other_residents_things']->id,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'no_smoking' => true,
            'no_pets' => true,
            'no_visitors' => true,
            'adults_only' => true,
            'no_noise_after_time' => true,
            'quiet' => true,
            'no_wash_night' => true,
            'no_light_night' => true,
            'entry_time_limit' => true,
            'cleaning_rules' => true,
            'cleaning_schedule' => true,
            'shoes_off' => true,
            'no_alcohol' => true,
            'no_parties' => true,
            'no_outsiders' => true,
            'no_loud_music' => true,
            'no_food_room' => true,
            'no_eating_bed' => true,
            'no_place_change' => true,
            'no_other_shelves' => true,
            'no_other_things' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Matched Restrictive Rules Place');
        $response->assertDontSee('Filtered Restrictive Rules Place');
    }

    public function test_rule_criteria_search_uses_existing_rule_indexes(): void
    {
        $this->assertTrue(Schema::hasIndex('rules', ['slug']));
        $this->assertTrue(Schema::hasIndex('property_rule', ['property_id', 'rule_id']));
        $this->assertTrue(Schema::hasIndex('property_rule', ['rule_id']));
        $this->assertTrue(Schema::hasIndex('room_rule', ['room_id', 'rule_id']));
        $this->assertTrue(Schema::hasIndex('room_rule', ['rule_id']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_rule', ['sleeping_place_id', 'rule_id']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_rule', ['rule_id']));
    }

    public function test_search_filters_by_neighbor_compatibility_criteria(): void
    {
        $city = $this->city('Neighbor Criteria City');

        $matched = $this->createSearchPlace('Matched Neighbor Place', $city, [], [
            'current_residents_count' => 4,
            'current_guests_count' => 4,
        ], [
            'current_guests_count' => 2,
            'occupied_sleeping_places_count' => 2,
            'gender_policy' => GenderType::Mixed->value,
            'gender_type' => GenderType::Mixed->value,
        ]);
        $this->createSearchPlace('Filtered Neighbor Place', $city);

        PropertyCurrentOccupancySnapshot::factory()->create([
            'property_id' => $matched->property_id,
            'host_user_id' => $matched->property->host_user_id,
            'current_occupants_count' => 4,
        ]);
        RoomCurrentOccupancySnapshot::factory()->create([
            'room_id' => $matched->room_id,
            'property_id' => $matched->property_id,
            'host_user_id' => $matched->property->host_user_id,
            'current_occupants_count' => 2,
            'male_occupants_count' => 1,
            'female_occupants_count' => 1,
            'students_count' => 1,
            'workers_count' => 1,
            'tourists_count' => 1,
            'long_term_residents_count' => 1,
            'early_wakeup_count' => 1,
            'late_sleep_count' => 1,
            'night_work_count' => 1,
            'smokers_count' => 1,
            'quiet_preferring_count' => 1,
            'social_count' => 1,
        ]);
        RoomCompatibilityProfile::factory()->for($matched->room)->create([
            'pets_present' => true,
        ]);
        RoomRatingSnapshot::factory()->create([
            'room_id' => $matched->room_id,
            'property_id' => $matched->property_id,
            'host_user_id' => $matched->property->host_user_id,
            'roommate_experience_rating' => 4.7,
            'reviews_count' => 5,
        ]);
        RoomOccupantSnapshot::factory()->create([
            'room_id' => $matched->room_id,
            'sleeping_place_id' => $matched->id,
            'status' => RoomOccupantSnapshot::STATUS_CURRENT,
            'check_in_date' => '2026-06-01',
            'check_out_date' => '2026-08-01',
            'age_range_snapshot' => '25-34',
            'gender_for_room_policy_snapshot' => GenderType::Male->value,
            'languages_json_snapshot' => ['en', 'ru'],
            'student_snapshot' => true,
            'working_snapshot' => true,
            'tourist_snapshot' => true,
            'long_term_guest_snapshot' => true,
            'sleep_schedule_snapshot' => 'night_owl',
            'wake_schedule_snapshot' => 'early',
            'home_presence_level_snapshot' => 'often_home',
            'smokes_snapshot' => true,
            'has_pet_snapshot' => true,
            'social_level_snapshot' => 'social',
            'prefers_quiet_snapshot' => true,
            'can_show_before_booking' => true,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'roommates_max' => 2,
            'residents_max' => 4,
            'neighbor_age' => '25-34',
            'neighbor_lifestyle' => 'work_study',
            'neighbor_language' => 'ru',
            'neighbor_rating' => '4.5',
            'n_students' => true,
            'n_workers' => true,
            'n_tourists' => true,
            'n_long_stay' => true,
            'n_quiet' => true,
            'n_social' => true,
            'n_often_home' => true,
            'n_work_night' => true,
            'n_early' => true,
            'n_late_sleep' => true,
            'n_smoke' => true,
            'n_pets' => true,
            'n_male' => true,
            'n_female' => true,
            'n_mixed' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Matched Neighbor Place');
        $response->assertDontSee('Filtered Neighbor Place');
    }

    public function test_search_filters_by_neighbor_zero_and_negative_criteria(): void
    {
        $city = $this->city('Quiet Empty Neighbor City');

        $matched = $this->createSearchPlace('Quiet Empty Neighbor Place', $city, [], [
            'current_residents_count' => 0,
            'current_guests_count' => 0,
        ], [
            'current_guests_count' => 0,
            'occupied_sleeping_places_count' => 0,
        ]);
        $filtered = $this->createSearchPlace('Smoker Pet Neighbor Place', $city, [], [
            'current_residents_count' => 1,
            'current_guests_count' => 1,
        ], [
            'current_guests_count' => 1,
            'occupied_sleeping_places_count' => 1,
        ]);

        PropertyCurrentOccupancySnapshot::factory()->create([
            'property_id' => $matched->property_id,
            'host_user_id' => $matched->property->host_user_id,
            'current_occupants_count' => 0,
        ]);
        RoomCurrentOccupancySnapshot::factory()->create([
            'room_id' => $matched->room_id,
            'property_id' => $matched->property_id,
            'host_user_id' => $matched->property->host_user_id,
            'current_occupants_count' => 0,
            'smokers_count' => 0,
        ]);
        RoomCompatibilityProfile::factory()->for($matched->room)->create([
            'pets_present' => false,
        ]);

        PropertyCurrentOccupancySnapshot::factory()->create([
            'property_id' => $filtered->property_id,
            'host_user_id' => $filtered->property->host_user_id,
            'current_occupants_count' => 1,
        ]);
        RoomCurrentOccupancySnapshot::factory()->create([
            'room_id' => $filtered->room_id,
            'property_id' => $filtered->property_id,
            'host_user_id' => $filtered->property->host_user_id,
            'current_occupants_count' => 1,
            'smokers_count' => 1,
        ]);
        RoomCompatibilityProfile::factory()->for($filtered->room)->create([
            'pets_present' => true,
        ]);
        RoomOccupantSnapshot::factory()->create([
            'room_id' => $filtered->room_id,
            'sleeping_place_id' => $filtered->id,
            'status' => RoomOccupantSnapshot::STATUS_CURRENT,
            'check_in_date' => '2026-06-01',
            'check_out_date' => '2026-08-01',
            'smokes_snapshot' => true,
            'has_pet_snapshot' => true,
            'can_show_before_booking' => true,
        ]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'roommates_max' => 0,
            'residents_max' => 0,
            'neighbor_age' => 'unknown',
            'neighbor_language' => 'xx',
            'neighbor_lifestyle' => 'party',
            'neighbor_rating' => '7',
            'n_no_smoke' => true,
            'n_no_pets' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Quiet Empty Neighbor Place');
        $response->assertDontSee('Smoker Pet Neighbor Place');
    }

    public function test_neighbor_criteria_search_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('room_occupant_snapshots', 'has_pet_snapshot'));
        $this->assertTrue(Schema::hasIndex('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'check_in_date', 'check_out_date']));
        $this->assertTrue(Schema::hasIndex('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'age_range_snapshot']));
        $this->assertTrue(Schema::hasIndex('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'gender_for_room_policy_snapshot']));
        $this->assertTrue(Schema::hasIndex('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'has_pet_snapshot']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['students_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['workers_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['tourists_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['long_term_residents_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['male_occupants_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['female_occupants_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['early_wakeup_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['late_sleep_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['night_work_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['smokers_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['non_smokers_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['quiet_preferring_count']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['social_count']));
        $this->assertTrue(Schema::hasIndex('room_rating_snapshots', ['roommate_experience_rating', 'reviews_count']));
    }

    public function test_search_filters_by_safety_criteria(): void
    {
        $city = $this->city('Safety Criteria City');
        $matched = $this->createSearchPlace('Matched Safety Place', $city, [
            'has_locker' => true,
            'has_lockable_locker' => true,
            'locker_has_lock' => true,
        ], [
            'review_status' => 'approved',
            'reviewed_at' => now(),
            'has_security' => true,
            'has_cctv_common_areas' => true,
            'show_exact_address_after_confirmation' => false,
            'show_exact_address_after_payment' => false,
            'emergency_contact_name' => 'Emergency Host',
            'emergency_contact_phone' => '+37060000000',
        ], [
            'has_lock' => true,
            'has_lockable_door' => true,
            'has_room_key' => true,
            'has_lockers' => true,
        ]);
        $this->createSearchPlace('Filtered Safety Place', $city);

        PropertyAddress::factory()->for($matched->property)->create([
            'show_exact_address_after_booking' => true,
        ]);
        PropertyAccessDetail::factory()->for($matched->property)->create([
            'entrance_type' => 'electronic_lock',
            'has_key' => true,
            'has_door_code' => true,
            'has_electronic_lock' => true,
            'has_keycard' => true,
            'has_smart_lock' => true,
            'has_intercom' => true,
            'has_intercom_code' => true,
            'has_key_safe' => true,
            'emergency_contact_available' => true,
        ]);
        HostProfile::query()
            ->where('user_id', $matched->property->host_user_id)
            ->firstOrFail()
            ->forceFill([
                'verified_host' => true,
                'response_time_minutes' => 20,
                'can_help_with_check_in' => true,
                'emergency_contact_available' => true,
            ])
            ->save();

        $verifiedGuest = User::factory()->create([
            'identity_verified' => true,
            'identity_verified_at' => now(),
        ]);
        RoomOccupantSnapshot::factory()->create([
            'user_id' => $verifiedGuest->id,
            'room_id' => $matched->room_id,
            'sleeping_place_id' => $matched->id,
            'status' => RoomOccupantSnapshot::STATUS_CURRENT,
            'check_in_date' => '2026-06-01',
            'check_out_date' => '2026-08-01',
            'can_show_before_booking' => true,
        ]);

        SleepingPlaceRatingSnapshot::factory()->create([
            'sleeping_place_id' => $matched->id,
            'room_id' => $matched->room_id,
            'property_id' => $matched->property_id,
            'host_user_id' => $matched->property->host_user_id,
            'safety_rating' => 4.8,
            'reviews_count' => 5,
        ]);
        RoomRatingSnapshot::factory()->create([
            'room_id' => $matched->room_id,
            'property_id' => $matched->property_id,
            'host_user_id' => $matched->property->host_user_id,
            'safety_rating' => 4.7,
            'reviews_count' => 5,
        ]);
        PropertyRatingSnapshot::factory()->create([
            'property_id' => $matched->property_id,
            'host_user_id' => $matched->property->host_user_id,
            'safety_rating' => 4.9,
            'reviews_count' => 5,
        ]);

        $amenities = $this->amenities([
            'safe',
            'no_private_area_cameras',
            'first_aid_kit',
            'fire_extinguisher',
            'smoke_detector',
            'gas_detector',
            'fire_safety_instructions',
            'emergency_exit',
            'urgent_help_available',
        ]);
        $matched->property->amenities()->attach(collect($amenities)->pluck('id')->all());

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            's_v_host' => true,
            's_v_property' => true,
            's_v_guests' => true,
            's_room_lock' => true,
            's_entry_lock' => true,
            's_locker' => true,
            's_safe' => true,
            's_guard' => true,
            's_intercom' => true,
            's_cctv' => true,
            's_no_private_cams' => true,
            's_first_aid' => true,
            's_fire_ext' => true,
            's_smoke_det' => true,
            's_gas_det' => true,
            's_fire_instr' => true,
            's_exit' => true,
            's_address_after' => true,
            's_emergency_contact' => true,
            's_urgent_help' => true,
            's_good_rating' => true,
            's_no_serious' => true,
            's_no_theft' => true,
            's_no_aggression' => true,
            's_no_dirt' => true,
            's_no_fraud' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Matched Safety Place');
        $response->assertDontSee('Filtered Safety Place');
    }

    public function test_search_filters_out_active_safety_complaints(): void
    {
        $city = $this->city('Safety Complaint City');
        $clean = $this->createSearchPlace('Clean Safety Place', $city);
        $theft = $this->createSearchPlace('Theft Complaint Place', $city);
        $aggression = $this->createSearchPlace('Aggression Complaint Place', $city);
        $dirty = $this->createSearchPlace('Dirty Complaint Place', $city);
        $fraud = $this->createSearchPlace('Fraud Complaint Place', $city);

        ComplaintCase::factory()->create([
            'property_id' => $clean->property_id,
            'room_id' => $clean->room_id,
            'sleeping_place_id' => $clean->id,
            'host_user_id' => $clean->property->host_user_id,
            'complaint_type' => 'theft',
            'severity' => 'critical',
            'status' => 'closed',
        ]);

        foreach ([
            [$theft, 'theft'],
            [$aggression, 'aggression'],
            [$dirty, 'dirty_room'],
            [$fraud, 'fraud'],
        ] as [$place, $type]) {
            ComplaintCase::factory()->create([
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
                'sleeping_place_id' => $place->id,
                'host_user_id' => $place->property->host_user_id,
                'complaint_type' => $type,
                'severity' => 'high',
                'status' => 'submitted',
            ]);
        }

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            's_no_serious' => true,
            's_no_theft' => true,
            's_no_aggression' => true,
            's_no_dirt' => true,
            's_no_fraud' => true,
        ]));

        $response->assertOk();
        $response->assertSee('Clean Safety Place');
        $response->assertDontSee('Theft Complaint Place');
        $response->assertDontSee('Aggression Complaint Place');
        $response->assertDontSee('Dirty Complaint Place');
        $response->assertDontSee('Fraud Complaint Place');
    }

    public function test_safety_criteria_search_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasIndex('properties', ['status', 'review_status']));
        $this->assertTrue(Schema::hasIndex('properties', ['status', 'has_security']));
        $this->assertTrue(Schema::hasIndex('properties', ['status', 'has_cctv_common_areas']));
        $this->assertTrue(Schema::hasIndex('property_addresses', ['show_exact_address_after_booking', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['has_key', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['has_intercom', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['has_key_safe', 'property_id']));
        $this->assertTrue(Schema::hasIndex('property_access_details', ['emergency_contact_available', 'property_id']));
        $this->assertTrue(Schema::hasIndex('host_profiles', ['verified_host', 'user_id']));
        $this->assertTrue(Schema::hasIndex('host_profiles', ['response_time_minutes', 'user_id']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_rating_snapshots', ['safety_rating', 'reviews_count']));
        $this->assertTrue(Schema::hasIndex('room_rating_snapshots', ['safety_rating', 'reviews_count']));
        $this->assertTrue(Schema::hasIndex('property_rating_snapshots', ['safety_rating', 'reviews_count']));
        $this->assertTrue(Schema::hasIndex('complaint_cases', ['sleeping_place_id', 'status', 'complaint_type']));
        $this->assertTrue(Schema::hasIndex('complaint_cases', ['room_id', 'status', 'complaint_type']));
        $this->assertTrue(Schema::hasIndex('complaint_cases', ['property_id', 'status', 'complaint_type']));
        $this->assertTrue(Schema::hasIndex('complaint_cases', ['host_user_id', 'status', 'complaint_type']));
        $this->assertTrue(Schema::hasIndex('complaints', ['sleeping_place_id', 'status', 'type']));
        $this->assertTrue(Schema::hasIndex('complaints', ['room_id', 'status', 'type']));
        $this->assertTrue(Schema::hasIndex('complaints', ['property_id', 'status', 'type']));
        $this->assertTrue(Schema::hasIndex('complaints', ['reported_user_id', 'status', 'type']));
    }

    public function test_search_url_state_initializes_filters(): void
    {
        $city = $this->city('Warsaw');
        $this->createSearchPlace('Warsaw Place', $city, ['base_price_per_night' => 20]);

        $response = $this->get(route('search.index', [
            'locale' => 'en',
            'city' => $city->id,
            'price_max' => 30,
            'guests' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Warsaw');
        $response->assertSee('Warsaw Place');

        Livewire::test(SleepingPlaceSearch::class)
            ->set('cityQuery', 'War')
            ->set('priceMax', '30')
            ->set('wifi', true)
            ->assertSet('city', 'War')
            ->assertSet('priceMax', '30')
            ->assertSet('wifi', true);

        Livewire::test(SleepingPlaceSearch::class)
            ->set('withBalcony', true)
            ->set('withoutElevator', true)
            ->set('courtyardWindows', true)
            ->set('privateRoom', true)
            ->set('roomUpToTwoGuests', true)
            ->set('roomUpToSixGuests', true)
            ->set('passThroughRoom', true)
            ->set('smokingAllowed', true)
            ->set('visitorsAllowed', true)
            ->set('noWashingAtNight', true)
            ->set('noOtherPeopleThings', true)
            ->set('neighborRoommatesMax', '2')
            ->set('propertyResidentsMax', '5')
            ->set('neighborAgeRange', '25-34')
            ->set('neighborLifestyle', 'quiet')
            ->set('neighborLanguage', 'ru')
            ->set('neighborMinRating', '4.5')
            ->set('neighborStudents', true)
            ->set('neighborsDoNotSmoke', true)
            ->set('mixedNeighborGenders', true)
            ->set('nearMetro', true)
            ->set('cleanProperty', true)
            ->set('doorCodeAccess', true)
            ->assertSet('withBalcony', true)
            ->assertSet('withoutElevator', true)
            ->assertSet('courtyardWindows', true)
            ->assertSet('privateRoom', true)
            ->assertSet('roomUpToTwoGuests', true)
            ->assertSet('roomUpToSixGuests', true)
            ->assertSet('passThroughRoom', true)
            ->assertSet('smokingAllowed', true)
            ->assertSet('visitorsAllowed', true)
            ->assertSet('noWashingAtNight', true)
            ->assertSet('noOtherPeopleThings', true)
            ->assertSet('neighborRoommatesMax', '2')
            ->assertSet('propertyResidentsMax', '5')
            ->assertSet('neighborAgeRange', '25-34')
            ->assertSet('neighborLifestyle', 'quiet')
            ->assertSet('neighborLanguage', 'ru')
            ->assertSet('neighborMinRating', '4.5')
            ->assertSet('neighborStudents', true)
            ->assertSet('neighborsDoNotSmoke', true)
            ->assertSet('mixedNeighborGenders', true)
            ->assertSet('nearMetro', true)
            ->assertSet('cleanProperty', true)
            ->assertSet('doorCodeAccess', true)
            ->call('clearFilters')
            ->assertSet('neighborRoommatesMax', '')
            ->assertSet('propertyResidentsMax', '')
            ->assertSet('neighborAgeRange', '')
            ->assertSet('neighborLifestyle', '')
            ->assertSet('neighborLanguage', '')
            ->assertSet('neighborMinRating', '')
            ->assertSet('neighborStudents', false)
            ->assertSet('neighborsDoNotSmoke', false)
            ->assertSet('mixedNeighborGenders', false)
            ->assertSet('nearMetro', false)
            ->assertSet('cleanProperty', false)
            ->assertSet('doorCodeAccess', false)
            ->assertSet('guestsCount', 1);

        $safetyProperties = [
            'safetyVerifiedHost',
            'safetyVerifiedProperty',
            'safetyVerifiedGuests',
            'safetyRoomLock',
            'safetyEntranceLock',
            'safetyPersonalLocker',
            'safetySafe',
            'safetySecurityGuard',
            'safetyIntercom',
            'safetyCctvCommonAreas',
            'safetyNoPrivateCameras',
            'safetyFirstAidKit',
            'safetyFireExtinguisher',
            'safetySmokeDetector',
            'safetyGasDetector',
            'safetyFireInstructions',
            'safetyEmergencyExit',
            'safetyExactAddressAfterBooking',
            'safetyEmergencyContact',
            'safetyUrgentSupport',
            'safetyGoodRating',
            'safetyNoSeriousComplaints',
            'safetyNoTheftComplaints',
            'safetyNoAggressionComplaints',
            'safetyNoDirtComplaints',
            'safetyNoFraudComplaints',
        ];

        $component = Livewire::test(SleepingPlaceSearch::class);

        foreach ($safetyProperties as $property) {
            $component->set($property, true)
                ->assertSet($property, true);
        }

        $component->call('clearFilters');

        foreach ($safetyProperties as $property) {
            $component->assertSet($property, false);
        }
    }

    public function test_search_results_use_lower_bound_total_until_filter_sheet_needs_exact_count(): void
    {
        $city = $this->city('Lower Bound City');

        for ($i = 1; $i <= 14; $i++) {
            $this->createSearchPlace("Lower Bound Place {$i}", $city, ['base_price_per_night' => 25]);
        }

        Livewire::test(SleepingPlaceSearch::class)
            ->set('city', (string) $city->id)
            ->assertViewHas('results', function (array $results): bool {
                return count($results['cards']) === 12
                    && $results['showing'] === 12
                    && $results['has_more'] === true
                    && $results['total'] === 13
                    && $results['total_is_exact'] === false;
            })
            ->assertSee(__('search.summary.matched_results_lower_bound', ['count' => 13]))
            ->assertDontSee(trans_choice('search.summary.matched_results', 14, ['count' => 14]));
    }

    public function test_filter_sheet_result_count_tracks_total_matching_places_after_filter_changes(): void
    {
        $city = $this->city('Count City');

        for ($i = 1; $i <= 13; $i++) {
            $this->createSearchPlace("Count Place {$i}", $city, ['base_price_per_night' => 25]);
        }

        Livewire::test(SleepingPlaceSearch::class)
            ->set('filtersOpen', true)
            ->set('city', (string) $city->id)
            ->set('priceMax', '40')
            ->assertSee(__('search.actions.show_results', ['count' => 13]))
            ->assertDontSee(__('search.actions.show_results', ['count' => 0]));
    }

    public function test_search_results_are_localized(): void
    {
        $city = $this->city('Minsk');
        $this->createSearchPlace('English sleeping place', $city, [], [], [], 'Русское спальное место');

        $response = $this->get(route('search.index', ['locale' => 'ru', 'city' => $city->id]));

        $response->assertOk();
        $response->assertSee('Русское спальное место');
        $response->assertDontSee('English sleeping place');
    }

    public function test_search_empty_state_is_friendly(): void
    {
        $city = $this->city('Empty City');

        $response = $this->get(route('search.index', ['locale' => 'en', 'city' => $city->id]));

        $response->assertOk();
        $response->assertSee(__('search.empty.title'));
        $response->assertSee(__('search.empty.change_dates'));
        $response->assertSee(__('search.empty.increase_budget'));
        $response->assertSee(__('search.empty.nearby_cities'));
        $response->assertSee(__('search.empty.fewer_filters'));
    }

    public function test_bed_detail_page_loads(): void
    {
        $host = User::factory()->create();
        $property = Property::factory()->for($host, 'host')->create(['status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => 'active']);
        $bed = Bed::factory()->for($room)->create(['status' => 'active', 'title' => 'Cozy Single']);

        $response = $this->get(route('beds.show', ['locale' => 'en', 'bed' => $bed]));

        $response->assertStatus(200);
        $response->assertSee('Cozy Single');
    }

    public function test_bed_detail_shows_host_name(): void
    {
        $host = User::factory()->create(['name' => 'Jane Host']);
        $property = Property::factory()->for($host, 'host')->create(['status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => 'active']);
        $bed = Bed::factory()->for($room)->create(['status' => 'active']);

        $response = $this->get(route('beds.show', ['locale' => 'en', 'bed' => $bed]));

        $response->assertStatus(200);
        $response->assertSee('Jane Host');
    }

    public function test_bed_detail_shows_guest_compatibility_block(): void
    {
        $guest = User::factory()->create();
        $guest->guestPreference()->create([
            'preferred_currency' => 'EUR',
            'preferred_budget_max' => 40,
            'wants_wifi' => true,
            'needs_quiet_hours' => true,
        ]);

        $host = User::factory()->create(['name' => 'Jane Host']);
        $property = Property::factory()->for($host, 'host')->create([
            'status' => 'active',
            'amenities' => ['wifi'],
            'rules' => ['quiet_hours', 'no_smoking'],
        ]);
        $room = Room::factory()->for($property)->create(['status' => 'active']);
        $bed = Bed::factory()->for($room)->create([
            'status' => 'active',
            'price_per_night' => 25,
        ]);

        $response = $this->actingAs($guest)->get(route('beds.show', ['locale' => 'en', 'bed' => $bed]));

        $response->assertOk();
        $response->assertSee('Why this place fits');
        $response->assertSee('Pay attention');
        $response->assertSee('Wi-Fi is available.');
    }

    private function city(string $name): City
    {
        $country = Country::query()->firstOrCreate(
            ['iso2' => 'DE'],
            [
                'code' => 'DE',
                'iso3' => 'DEU',
                'name' => 'Germany',
                'name_en' => 'Germany',
                'status' => Country::STATUS_ACTIVE,
                'is_active' => true,
            ],
        );

        return City::factory()->for($country)->create([
            'name' => $name,
            'ascii_name' => $name,
            'status' => City::STATUS_ACTIVE,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $placeAttributes
     * @param  array<string, mixed>  $propertyAttributes
     * @param  array<string, mixed>  $roomAttributes
     */
    private function createSearchPlace(
        string $title,
        City $city,
        array $placeAttributes = [],
        array $propertyAttributes = [],
        array $roomAttributes = [],
        ?string $ruTitle = null,
    ): SleepingPlace {
        $host = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'rating_average' => 4.8,
            'reviews_count' => 12,
            'verified_at' => now(),
        ]);

        $entranceType = $propertyAttributes['entrance_type'] ?? null;
        unset($propertyAttributes['entrance_type']);

        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create(array_merge([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $city->country_id,
                'city_id' => $city->id,
                'city' => $city->name,
                'district' => 'Central',
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
            ], $propertyAttributes));
        $room = Room::factory()->for($property)->create(array_merge([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::NoRestriction,
            'max_guests' => 4,
            'available_places_count' => 1,
        ], $roomAttributes));
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create(array_merge([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::Single,
                'display_name' => $title,
                'base_price_per_night' => 30,
                'currency' => 'EUR',
                'max_guests' => 1,
                'min_nights' => 1,
                'max_nights' => null,
            ], $placeAttributes));

        $place->translations()->create(['locale' => 'en', 'title' => $title]);
        $place->translations()->create(['locale' => 'ru', 'title' => $ruTitle ?: $title]);

        if (is_string($entranceType) && $entranceType !== '') {
            PropertyAccessDetail::factory()->for($property)->create([
                'entrance_type' => $entranceType,
                'has_private_entrance' => $entranceType === 'private_entrance',
                'has_shared_entrance' => $entranceType === 'shared_entrance',
            ]);
        }

        return $place;
    }

    private function amenity(string $slug, string $label): Amenity
    {
        $amenity = Amenity::factory()->create([
            'slug' => $slug,
            'name_normalized' => $slug,
            'category' => 'property',
            'status' => 'active',
        ]);
        $amenity->translations()->create(['locale' => 'en', 'name' => $label, 'name_normalized' => str($label)->lower()->toString()]);
        $amenity->translations()->create(['locale' => 'ru', 'name' => $label, 'name_normalized' => str($label)->lower()->toString()]);

        return $amenity;
    }

    /**
     * @param  list<string>  $slugs
     * @return array<string, Amenity>
     */
    private function amenities(array $slugs): array
    {
        return collect($slugs)
            ->mapWithKeys(fn (string $slug): array => [$slug => $this->amenity($slug, str($slug)->replace('_', ' ')->title()->toString())])
            ->all();
    }

    /**
     * @param  list<string>  $slugs
     * @return array<string, Rule>
     */
    private function rules(array $slugs): array
    {
        return collect($slugs)
            ->mapWithKeys(fn (string $slug): array => [$slug => $this->rule($slug)])
            ->all();
    }

    private function rule(string $slug): Rule
    {
        $label = str($slug)->replace('_', ' ')->title()->toString();

        $rule = Rule::factory()->create([
            'slug' => $slug,
            'name_normalized' => str($slug)->replace('_', ' ')->lower()->toString(),
            'category' => 'shared_room_behavior',
            'status' => 'active',
        ]);

        $rule->translations()->create([
            'locale' => 'en',
            'name' => $label,
            'name_normalized' => str($label)->lower()->toString(),
        ]);
        $rule->translations()->create([
            'locale' => 'ru',
            'name' => $label,
            'name_normalized' => str($label)->lower()->toString(),
        ]);

        return $rule;
    }
}
