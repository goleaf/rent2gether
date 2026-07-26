<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Places\ShowSleepingPlace;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PublicSleepingPlaceDetailTest extends TestCase
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

    public function test_detail_renders_in_english_and_russian(): void
    {
        $place = $this->createPlace('Central lower bed', 'Нижнее место в центре');

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee('Central lower bed')
            ->assertSee(__('listing.detail.summary.title'));

        $this->get(route('places.show', ['locale' => 'ru', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee('Нижнее место в центре')
            ->assertSee('Кратко о месте');
    }

    public function test_detail_page_leads_with_price_and_complete_decision_context(): void
    {
        $place = $this->createPlace('Decision place');

        $response = $this->get(route('places.show', [
            'locale' => 'en',
            'sleepingPlace' => $place,
            'in' => '2026-07-10',
            'out' => '2026-07-13',
            'guests' => 1,
        ]));

        $response
            ->assertOk()
            ->assertSee(__('listing.detail.flow.title'))
            ->assertSee(__('listing.detail.booking.price_breakdown_title'))
            ->assertSee(__('listing.detail.calendar.title'))
            ->assertSee(__('listing.detail.map.title'))
            ->assertSee(__('listing.detail.safety.title'))
            ->assertSee(__('listing.detail.cancellation.title'));

        $html = $response->getContent();
        $pricePosition = strpos($html, 'data-detail-section="price-breakdown"');
        $roomPosition = strpos($html, 'data-detail-section="room-details"');

        $this->assertNotFalse($pricePosition);
        $this->assertNotFalse($roomPosition);
        $this->assertLessThan($roomPosition, $pricePosition);
    }

    public function test_extended_instruction_sections_render_translated_content_with_field_fallbacks(): void
    {
        $this->assertTrue(Schema::hasColumn('property_translations', 'short_description'));
        $this->assertTrue(Schema::hasColumn('room_translations', 'room_description'));
        $this->assertTrue(Schema::hasColumn('sleeping_place_translations', 'sleeping_place_description'));

        $place = $this->createPlace('Instruction place', 'Место с инструкциями');

        $place->property->translations()->where('locale', 'en')->update([
            'short_description' => 'A calm place with clear house instructions.',
            'full_description' => 'You will sleep in a lower bunk inside a shared but quiet apartment.',
            'why_convenient' => 'The bus stop and grocery store are close.',
            'what_is_included' => 'Wi-Fi, bedding, utilities, and weekly kitchen cleaning are included.',
            'what_is_not_included' => 'Laundry powder and personal hygiene items are not included.',
            'what_to_bring' => 'Bring your passport, charger, slippers, and a small locker padlock.',
            'where_to_store_belongings' => 'Keep your suitcase under the lower bunk and valuables in the locker.',
            'kitchen_instructions' => 'Use the kitchen before 22:00 and wash dishes right away.',
            'bathroom_instructions' => 'Keep showers short at night and wipe the floor after use.',
            'laundry_instructions' => 'Wash clothes after 09:00 and dry them on the shared rack.',
            'key_pickup_instructions' => 'Private exact key safe code 1234 near the apartment door.',
            'host_contact_instructions' => 'Private host phone +370 600 00000.',
            'problem_instructions' => 'Message the host in the app if something feels wrong.',
            'lost_key_instructions' => 'Tell the host in the app if a key is lost.',
            'neighbor_conflict_instructions' => 'Step away first, then message the host about the conflict.',
            'repair_problem_instructions' => 'Tell the host if something breaks or stops working.',
        ]);

        $place->property->translations()->where('locale', 'ru')->update([
            'short_description' => 'Спокойное место с понятными правилами проживания.',
            'what_is_included' => null,
        ]);

        $place->room->translations()->where('locale', 'en')->update([
            'room_description' => 'The room is shared by up to four guests.',
            'who_lives_nearby_text' => 'Only the guest count is shown before booking.',
            'storage_instructions' => 'Use the shelf beside the bed for daily items.',
            'shared_space_instructions' => 'Keep shared paths clear for other guests.',
        ]);

        $place->translations()->where('locale', 'en')->update([
            'sleeping_place_description' => 'The lower bunk has a lamp, socket, shelf, and locker.',
            'sleeping_place_pros' => 'Lower bunk, easy access, and calmer corner.',
            'what_is_included_for_place' => 'Lamp, socket, bedding, towel, and locker are included for this place.',
            'what_guest_should_bring_for_place' => 'Bring earplugs if you are sensitive to shared-room noise.',
        ]);

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee(__('listing_detail.sections.description'))
            ->assertSee(__('listing_detail.sections.included'))
            ->assertSee(__('listing_detail.sections.storage'))
            ->assertSee(__('listing_detail.sections.kitchen'))
            ->assertSee(__('listing_detail.sections.bathroom'))
            ->assertSee(__('listing_detail.sections.laundry'))
            ->assertSee(__('listing_detail.sections.keys'))
            ->assertSee(__('listing_detail.sections.problem'))
            ->assertSee('Wi-Fi, bedding, utilities, and weekly kitchen cleaning are included.')
            ->assertSee('Bring your passport, charger, slippers, and a small locker padlock.')
            ->assertSee('Use the kitchen before 22:00 and wash dishes right away.')
            ->assertSee('Message the host in the app if something feels wrong.')
            ->assertDontSee('Private host phone +370 600 00000')
            ->assertDontSee('Private exact key safe code 1234 near the apartment door.')
            ->assertDontSee(__('listing_detail.sections.food_storage'));

        $this->get(route('places.show', ['locale' => 'ru', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee('Спокойное место с понятными правилами проживания.')
            ->assertSee('Wi-Fi, bedding, utilities, and weekly kitchen cleaning are included.');
    }

    public function test_selected_dates_update_price_without_storing_quote_in_public_snapshot(): void
    {
        $place = $this->createPlace('Price test place');

        $component = Livewire::test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15')
            ->assertViewHas('priceBreakdown', function (array $priceBreakdown): bool {
                return $priceBreakdown['has_quote'] === true
                    && $priceBreakdown['total'] !== null
                    && $priceBreakdown['remaining_dates_count'] === 0
                    && count($priceBreakdown['date_prices']) === 5;
            })
            ->refresh()
            ->assertViewHas('priceBreakdown', function (array $priceBreakdown): bool {
                return $priceBreakdown['has_quote'] === true
                    && $priceBreakdown['total'] !== null
                    && $priceBreakdown['remaining_dates_count'] === 0
                    && count($priceBreakdown['date_prices']) === 5;
            });

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('sleepingPlaceId', $encodedSnapshot);
        $this->assertStringNotContainsString('"quote"', $encodedSnapshot);
        $this->assertStringNotContainsString('"line_items"', $encodedSnapshot);
        $this->assertStringNotContainsString('"availabilityWarning"', $encodedSnapshot);
        $this->assertStringNotContainsString('"unavailableDates"', $encodedSnapshot);
        $this->assertLessThan(20_000, strlen($encodedSnapshot), 'Sleeping-place detail snapshot should keep calculated booking preview data out of public Livewire state.');
    }

    public function test_detail_reuses_prefetched_availability_days_for_price_and_calendar_preview(): void
    {
        $place = $this->createPlace('Availability query place');
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-12',
            'status' => AvailabilityStatus::Available,
            'price_override' => 45,
        ]);

        $availabilityDaySelects = 0;

        DB::listen(static function ($query) use (&$availabilityDaySelects): void {
            $sql = strtolower($query->sql);

            if (str_starts_with($sql, 'select') && str_contains($sql, 'from "availability_days"')) {
                $availabilityDaySelects++;
            }
        });

        Livewire::withQueryParams([
            'in' => '2026-07-10',
            'out' => '2026-07-15',
        ])
            ->test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->assertViewHas('priceBreakdown', function (array $priceBreakdown): bool {
                return $priceBreakdown['has_quote'] === true
                    && collect($priceBreakdown['date_prices'])->contains(
                        fn (array $datePrice): bool => $datePrice['source'] === __('listing.detail.booking.price_sources.date_override')
                    );
            });

        $this->assertLessThanOrEqual(1, $availabilityDaySelects, 'Sleeping-place detail should reuse the prefetched availability days for quote pricing and calendar preview rendering.');
    }

    public function test_unavailable_warning_is_shown(): void
    {
        $place = $this->createPlace('Blocked place');
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-12',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);

        Livewire::test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15')
            ->assertSee(__('listing.detail.booking.unavailable_title'));
    }

    public function test_favorite_toggles_for_sleeping_place(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Favorite place');

        Livewire::actingAs($guest)
            ->test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->call('toggleFavorite')
            ->assertSet('isFavorited', true);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
            'bed_id' => null,
        ]);

        Livewire::actingAs($guest)
            ->test(ShowSleepingPlace::class, ['sleepingPlace' => $place])
            ->call('toggleFavorite')
            ->assertSet('isFavorited', false);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
        ]);
    }

    public function test_host_card_is_shown(): void
    {
        $place = $this->createPlace('Hosted place', hostName: 'Mila Host');

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee('Mila Host')
            ->assertSee(__('listing.detail.host.title'));
    }

    public function test_privacy_safe_occupant_summary_is_shown(): void
    {
        $place = $this->createPlace('Privacy place');
        $otherGuest = User::factory()->create(['name' => 'Private Guest Name']);
        $otherPlace = SleepingPlace::factory()
            ->for($place->room)
            ->for($place->property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
            ]);

        Booking::factory()->create([
            'guest_user_id' => $otherGuest->id,
            'host_user_id' => $place->property->host_user_id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $otherPlace->id,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-15',
            'status' => BookingStatus::Confirmed,
        ]);

        $this->get(route('places.show', [
            'locale' => 'en',
            'sleepingPlace' => $place,
            'in' => '2026-07-11',
            'out' => '2026-07-13',
        ]))
            ->assertOk()
            ->assertSee('1 guest nearby')
            ->assertSee(__('listing.detail.nearby.privacy'))
            ->assertDontSee('Private Guest Name');
    }

    private function createPlace(string $title, ?string $ruTitle = null, string $hostName = 'Nina Host'): SleepingPlace
    {
        $city = $this->city('Vilnius');
        $host = User::factory()->create(['name' => $hostName, 'is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'display_name' => $hostName,
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
                'nearest_transport' => 'Bus stop nearby',
                'kitchens_count' => 1,
                'bathrooms_count' => 1,
                'showers_count' => 1,
            ]);
        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Shared apartment',
            'summary' => 'A calm apartment near the center.',
            'description' => 'A calm apartment near the center.',
        ]);
        $property->translations()->create([
            'locale' => 'ru',
            'title' => 'Общая квартира',
            'summary' => 'Спокойная квартира рядом с центром.',
            'description' => 'Спокойная квартира рядом с центром.',
        ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::NoRestriction,
            'beds_count' => 4,
            'max_guests' => 4,
            'occupied_places_count' => 0,
            'noise_level' => 'quiet',
        ]);
        $room->translations()->create([
            'locale' => 'en',
            'title' => 'Shared quiet room',
            'summary' => 'A quiet shared room.',
            'description' => 'A quiet shared room.',
        ]);
        $room->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихая общая комната',
            'summary' => 'Тихая общая комната.',
            'description' => 'Тихая общая комната.',
        ]);

        $quietRule = $this->rule('quiet_hours_after_22', 'quiet_hours', 'Quiet hours after 22:00', 'Тихие часы после 22:00');
        $room->rules()->attach($quietRule);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::BunkBottom,
                'display_name' => $title,
                'base_price_per_night' => 30,
                'cleaning_fee' => 5,
                'deposit_amount' => 30,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => null,
                'has_bedding' => true,
                'has_towel' => true,
                'has_locker' => true,
                'privacy_level' => 'moderate',
            ]);
        $place->translations()->create([
            'locale' => 'en',
            'title' => $title,
            'summary' => 'A comfortable lower bunk.',
            'description' => 'A comfortable lower bunk.',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => $ruTitle ?: $title,
            'summary' => 'Удобное нижнее место.',
            'description' => 'Удобное нижнее место.',
        ]);

        return $place;
    }

    private function city(string $name): City
    {
        $country = Country::query()->firstOrCreate(
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
        $region = Region::factory()->for($country)->create([
            'name' => 'Vilnius County',
        ]);

        return City::factory()->for($country)->for($region)->create([
            'name' => $name,
            'ascii_name' => $name,
            'status' => City::STATUS_ACTIVE,
            'is_active' => true,
        ]);
    }

    private function rule(string $slug, string $category, string $en, string $ru): Rule
    {
        $rule = Rule::factory()->create([
            'slug' => $slug,
            'category' => $category,
            'status' => 'active',
            'name_normalized' => str($en)->lower()->toString(),
        ]);

        $rule->translations()->create(['locale' => 'en', 'name' => $en, 'name_normalized' => str($en)->lower()->toString()]);
        $rule->translations()->create(['locale' => 'ru', 'name' => $ru, 'name_normalized' => str($ru)->lower()->toString()]);

        return $rule;
    }
}
