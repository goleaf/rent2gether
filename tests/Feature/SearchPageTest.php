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
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
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
            ->assertSet('noOtherPeopleThings', true);
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
