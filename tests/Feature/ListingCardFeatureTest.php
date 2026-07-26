<?php

namespace Tests\Feature;

use App\Data\Hints\HintContext;
use App\Data\Listings\ListingCardContext;
use App\Enums\AvailabilityStatus;
use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\ReviewStatus;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Search\SleepingPlaceSearch;
use App\Livewire\Waitlist\JoinWaitlistButton;
use App\Models\Amenity;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\GuestCompatibilityProfile;
use App\Models\GuestCompatibilityVisibilitySetting;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomCurrentOccupancySnapshot;
use App\Models\RoomOccupantSnapshot;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Hints\ListingHintCalculatorService;
use App\Services\Listings\ListingCardQueryService;
use App\Services\Listings\ListingCardService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListingCardFeatureTest extends TestCase
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

    public function test_listing_card_service_builds_translated_date_aware_card(): void
    {
        $place = $this->createPlace('Lower bunk near center', [
            'base_price_per_night' => 18,
            'weekly_price' => 110,
            'deposit_amount' => 30,
            'instant_booking_enabled' => true,
        ]);

        Review::factory()->for($place, 'sleepingPlace')->create([
            'bed_id' => null,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'status' => ReviewStatus::Published,
            'overall_rating' => 4.8,
            'cleanliness_rating' => 4.9,
            'safety_rating' => 4.7,
        ]);

        $context = $this->context(locale: 'ru', checkIn: '2026-07-10', checkOut: '2026-07-17');
        $loaded = app(ListingCardQueryService::class)->forComparison([$place->id], $context)->firstOrFail();

        $card = app(ListingCardService::class)->build($loaded, $context);

        $this->assertSame('RU Lower bunk near center', $card->title);
        $this->assertSame(7, $card->nightsCount);
        $this->assertSame(8, $card->calendarDaysCount);
        $this->assertSame(18.0, $card->pricePerNight);
        $this->assertNotNull($card->totalPrice);
        $this->assertTrue($card->hasDiscount);
        $this->assertTrue($card->hasDeposit);
        $this->assertTrue($card->hostVerified);
        $this->assertTrue($card->instantBookingEnabled);
        $this->assertTrue($card->isAvailable);
        $this->assertContains('verified_host', collect($card->badges)->pluck('key')->all());
    }

    public function test_listing_card_renders_required_advertisement_fields(): void
    {
        $place = $this->createPlace('Complete listing card', [
            'base_price_per_night' => 25,
            'weekly_price' => 150,
            'deposit_amount' => 45,
            'instant_booking_enabled' => true,
            'extensions_allowed' => true,
            'can_extend' => true,
        ]);

        PropertyAccessDetail::factory()->for($place->property)->create([
            'self_check_in_available' => true,
            'has_key_safe' => true,
            'has_electronic_lock' => false,
            'has_smart_lock' => false,
        ]);

        RoomCurrentOccupancySnapshot::factory()
            ->for($place->room)
            ->for($place->property)
            ->for($place->property->host, 'host')
            ->create([
                'current_occupants_count' => 2,
                'occupied_sleeping_places_count' => 2,
                'free_sleeping_places_count' => 2,
                'students_count' => 1,
                'workers_count' => 1,
            ]);

        Review::factory()->count(2)->for($place, 'sleepingPlace')->create([
            'bed_id' => null,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'status' => ReviewStatus::Published,
            'overall_rating' => 4.8,
            'cleanliness_rating' => 4.9,
            'safety_rating' => 4.7,
        ]);

        $place->amenities()->attach($this->amenity('wifi'));
        $place->rules()->attach($this->rule('quiet_hours'));

        $context = $this->context(checkIn: '2026-07-10', checkOut: '2026-07-17');
        $loaded = app(ListingCardQueryService::class)->forComparison([$place->id], $context)->firstOrFail();
        $card = app(ListingCardService::class)->build($loaded, $context)->toArray();

        $this->assertSame('Vilnius', $card['city_name']);
        $this->assertSame('Center', $card['district']);
        $this->assertSame(__('listing_card.values.apartment'), $card['property_type']);
        $this->assertSame(__('listing_card.values.shared'), $card['room_type']);
        $this->assertSame(__('listing_card.values.bunk_bottom'), $card['sleeping_place_type']);
        $this->assertSame(4, $card['room_places_count']);
        $this->assertSame(2, $card['room_available_places_count']);
        $this->assertSame(2, $card['room_occupied_places_count']);
        $this->assertTrue($card['has_discount']);
        $this->assertTrue($card['has_deposit']);
        $this->assertTrue($card['has_free_cancellation']);
        $this->assertTrue($card['host_verified']);
        $this->assertTrue($card['instant_booking_enabled']);
        $this->assertTrue($card['can_extend']);
        $this->assertTrue($card['self_check_in']);
        $this->assertStringContainsString('student', $card['people_in_room_summary']);
        $this->assertStringContainsString('worker', $card['people_in_room_summary']);

        $this->blade('<x-listings.card :card="$card" card-variant="search" />', [
            'card' => $card,
        ])
            ->assertSee('Complete listing card')
            ->assertSee(__('listing_card.city_label', ['city' => 'Vilnius']))
            ->assertSee(__('listing_card.district_label', ['district' => 'Center']))
            ->assertSee(__('listing_card.values.apartment'))
            ->assertSee(__('listing_card.values.shared'))
            ->assertSee(__('listing_card.values.bunk_bottom'))
            ->assertSee(trans_choice('listing_card.places_in_room', 4, ['count' => 4]))
            ->assertSee(__('listing_card.available_places', ['count' => 2]))
            ->assertSee(trans_choice('listing_card.stay_days', 7, ['count' => 7]))
            ->assertSee(trans_choice('listing_card.calendar_days', 8, ['count' => 8]))
            ->assertSee(__('listing_card.discount'))
            ->assertSee(__('listing_card.has_deposit'))
            ->assertSee(__('listing_card.free_cancellation'))
            ->assertSee(__('listing_card.verified_host'))
            ->assertSee(__('listing_card.instant_booking'))
            ->assertSee(__('listing_card.can_extend'))
            ->assertSee(__('listing_card.self_check_in'))
            ->assertSee(__('listing_card.rating_metric', ['label' => __('listing_card.ratings.cleanliness'), 'rating' => '4.9']))
            ->assertSee(__('listing_card.rating_metric', ['label' => __('listing_card.ratings.safety'), 'rating' => '4.7']))
            ->assertSee('Wifi')
            ->assertSee('Quiet Hours');
    }

    public function test_guest_hint_calculator_covers_prompted_search_hint_keys(): void
    {
        $guest = User::factory()->create();
        GuestCompatibilityProfile::factory()->for($guest, 'user')->create([
            'avoids_upper_bunk' => true,
            'needs_locker' => true,
        ]);
        GuestCompatibilityVisibilitySetting::factory()->for($guest, 'user')->create();

        $city = $this->city('Vilnius');
        $place = $this->createPlace(
            'Prompt hint place',
            [
                'base_price_per_night' => 20,
                'weekly_price' => 120,
                'monthly_price' => 420,
                'weekend_price' => 30,
                'deposit_amount' => 50,
                'instant_booking_enabled' => true,
                'requires_host_approval' => false,
                'extensions_allowed' => true,
                'can_extend' => true,
                'is_top_bunk' => true,
                'has_locker' => false,
                'type' => SleepingPlaceType::BunkTop->value,
                'sleeping_place_type' => SleepingPlaceType::BunkTop->value,
                'max_nights' => 60,
            ],
            [
                'show_exact_address_before_booking' => false,
                'cleanliness_level' => 'excellent',
                'safety_level' => 'excellent',
                'rules' => ['quiet_hours', 'no_smoking'],
            ],
            $city,
        );
        $this->createPlace('Area average comparison place', [
            'base_price_per_night' => 80,
        ], city: $city);

        $place->room->update([
            'available_places_count' => 1,
            'free_sleeping_places_count' => 1,
            'sleeping_places_count' => 4,
            'current_guests_count' => 3,
            'can_talk_at_night' => false,
            'rules' => ['quiet_hours'],
        ]);
        $place->property->host->hostProfile->update(['response_time_minutes' => 20]);
        $place->rules()->attach($this->rule('identity_verification_required'));

        Booking::factory()
            ->count(5)
            ->for($place, 'sleepingPlace')
            ->for($place->property)
            ->for($place->room)
            ->create([
                'bed_id' => null,
                'host_user_id' => $place->property->host_user_id,
            ]);

        RoomOccupantSnapshot::factory()
            ->count(3)
            ->for($place->room)
            ->for($place, 'sleepingPlace')
            ->create([
                'status' => RoomOccupantSnapshot::STATUS_CURRENT,
                'check_in_date' => '2026-07-01',
                'check_out_date' => '2026-08-01',
            ]);

        $calculator = app(ListingHintCalculatorService::class);
        $context = new HintContext(
            checkInDate: '2026-07-10',
            checkOutDate: '2026-08-09',
            nightsCount: 30,
            userId: $guest->id,
            locale: 'en',
            surface: 'card',
        );
        $keys = $calculator
            ->calculateStaticHints($place->fresh())
            ->merge($calculator->calculateDynamicHints($place->fresh(), $context))
            ->pluck('key')
            ->unique()
            ->values()
            ->all();

        foreach ([
            'cheaper_than_area_average',
            'often_booked',
            'one_place_left',
            'host_responds_fast',
            'high_cleanliness_rating',
            'people_already_in_room',
            'available_for_longer_stay',
            'weekend_price_change',
            'weekly_discount',
            'monthly_discount',
            'address_after_booking',
            'deposit_required',
            'identity_verification_required',
            'strict_quiet_hours',
            'criteria_mismatch',
        ] as $expectedHintKey) {
            $this->assertContains($expectedHintKey, $keys);
        }
    }

    public function test_search_listing_card_payload_renders_dynamic_guest_hints_without_snapshots(): void
    {
        $place = $this->createPlace('Dynamic search hint place', [
            'base_price_per_night' => 30,
            'weekly_price' => 180,
            'weekend_price' => 42,
            'deposit_amount' => 0,
            'extensions_allowed' => false,
            'can_extend' => false,
            'max_nights' => 30,
        ], [
            'rules' => [],
            'cleanliness_level' => 'normal',
            'safety_level' => 'normal',
        ]);
        $place->room->update([
            'available_places_count' => 1,
            'free_sleeping_places_count' => 1,
            'current_guests_count' => 0,
            'can_talk_at_night' => true,
            'rules' => [],
            'noise_level' => 'moderate',
        ]);
        $place->property->host->hostProfile->update([
            'response_time_minutes' => 120,
            'rating_average' => 0,
            'reviews_count' => 0,
            'verified_at' => null,
        ]);
        $place->property->host->update(['identity_verified' => false]);

        $context = $this->context(checkIn: '2026-07-10', checkOut: '2026-07-17');
        $loaded = app(ListingCardQueryService::class)->forComparison([$place->id], $context)->firstOrFail();
        $card = app(ListingCardService::class)->build($loaded, $context)->toArray();
        $hintKeys = collect($card['hints'])->pluck('key')->all();

        $this->assertContains('one_place_left', $hintKeys);
        $this->assertContains('weekly_discount', $hintKeys);
        $this->assertContains('weekend_price_change', $hintKeys);

        $this->blade('<x-listings.card :card="$card" card-variant="search" />', [
            'card' => $card,
        ])
            ->assertSee(__('guest_hints.messages.one_place_left'))
            ->assertSee(__('guest_hints.messages.weekly_discount'))
            ->assertSee(__('guest_hints.messages.weekend_price_change'));
    }

    public function test_listing_card_without_dates_shows_nightly_price_only(): void
    {
        $place = $this->createPlace('No dates place', ['base_price_per_night' => 22]);
        $context = $this->context(checkIn: null, checkOut: null);
        $loaded = app(ListingCardQueryService::class)->forComparison([$place->id], $context)->firstOrFail();

        $card = app(ListingCardService::class)->build($loaded, $context);

        $this->assertSame(22.0, $card->pricePerNight);
        $this->assertNull($card->totalPrice);
        $this->assertNull($card->isAvailable);

        $this->blade('<x-listings.card :card="$card" card-variant="search" />', [
            'card' => $card->toArray(),
        ])->assertSee(__('listing_card.choose_dates_for_total'));
    }

    public function test_unavailable_card_shows_waitlist_action(): void
    {
        $place = $this->createPlace('Blocked place');
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);

        $context = $this->context(checkIn: '2026-07-10', checkOut: '2026-07-12');
        $loaded = app(ListingCardQueryService::class)->forComparison([$place->id], $context)->firstOrFail();
        $card = app(ListingCardService::class)->build($loaded, $context);

        $this->assertFalse($card->isAvailable);
        $this->assertSame('unavailable', $card->availabilityStatus);

        $this->blade('<x-listings.card :card="$card" card-variant="search" />', [
            'card' => $card->toArray(),
        ])
            ->assertSee(__('listing_card.unavailable'))
            ->assertSeeLivewire(JoinWaitlistButton::class);
    }

    public function test_card_limits_amenities_rules_and_hides_exact_address(): void
    {
        $place = $this->createPlace('Privacy place', propertyOverrides: [
            'street' => 'Secret Street',
            'address_line_1' => 'Secret Street 12',
        ]);

        foreach (['wifi', 'fast_wifi', 'kitchen', 'washing_machine', 'personal_locker', 'workspace'] as $slug) {
            $place->amenities()->attach($this->amenity($slug));
        }

        foreach (['no_smoking', 'quiet_hours_after_22', 'no_parties', 'no_pets'] as $slug) {
            $place->rules()->attach($this->rule($slug));
        }

        $context = $this->context();
        $loaded = app(ListingCardQueryService::class)->forComparison([$place->id], $context)->firstOrFail();
        $card = app(ListingCardService::class)->build($loaded, $context);

        $this->assertLessThanOrEqual(4, count($card->keyAmenities));
        $this->assertLessThanOrEqual(3, count($card->keyRules));

        $this->blade('<x-listings.card :card="$card" card-variant="search" />', [
            'card' => $card->toArray(),
        ])
            ->assertDontSee('Secret Street')
            ->assertDontSee('Secret Street 12');
    }

    public function test_search_uses_reusable_listing_card(): void
    {
        $city = $this->city('Vilnius');
        $this->createPlace('Reusable search card', city: $city);

        $this->get(route('search.index', ['locale' => 'en', 'city' => $city->id]))
            ->assertOk()
            ->assertSee('data-listing-card', false)
            ->assertSee('Reusable search card')
            ->assertSee(__('listing_card.choose_dates_for_total'));

        Livewire::test(SleepingPlaceSearch::class)
            ->set('city', (string) $city->id)
            ->assertOk();
    }

    public function test_listing_card_translations_render_in_english_and_russian(): void
    {
        $place = $this->createPlace('Localized label place');

        $english = app(ListingCardService::class)->build(
            app(ListingCardQueryService::class)->forComparison([$place->id], $this->context(locale: 'en'))->firstOrFail(),
            $this->context(locale: 'en'),
        );

        $russian = app(ListingCardService::class)->build(
            app(ListingCardQueryService::class)->forComparison([$place->id], $this->context(locale: 'ru'))->firstOrFail(),
            $this->context(locale: 'ru'),
        );

        $this->blade('<x-listings.card :card="$card" card-variant="search" />', [
            'card' => $english->toArray(),
        ])->assertSee(__('listing_card.view_place', [], 'en'));

        app()->setLocale('ru');

        $this->blade('<x-listings.card :card="$card" card-variant="search" />', [
            'card' => $russian->toArray(),
        ])->assertSee(__('listing_card.view_place', [], 'ru'));
    }

    private function context(
        string $locale = 'en',
        ?string $checkIn = '2026-07-10',
        ?string $checkOut = '2026-07-13',
    ): ListingCardContext {
        return new ListingCardContext(
            userId: null,
            locale: $locale,
            currency: 'EUR',
            checkInDate: $checkIn,
            checkOutDate: $checkOut,
            guestsCount: 1,
            source: 'test',
        );
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

    /**
     * @param  array<string, mixed>  $placeOverrides
     * @param  array<string, mixed>  $propertyOverrides
     */
    private function createPlace(
        string $title,
        array $placeOverrides = [],
        array $propertyOverrides = [],
        ?City $city = null,
    ): SleepingPlace {
        $city ??= $this->city('Vilnius');
        $host = User::factory()->create(['is_host' => true, 'identity_verified' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'rating_average' => 4.8,
            'reviews_count' => 12,
            'verified_at' => now(),
            'default_cancellation_policy' => 'flexible',
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
                'district' => 'Center',
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
                'kitchens_count' => 1,
                ...$propertyOverrides,
            ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::NoRestriction,
            'max_guests' => 4,
            'beds_count' => 4,
            'occupied_places_count' => 2,
            'available_places_count' => 1,
        ]);
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::BunkBottom,
                'display_name' => $title,
                'base_price_per_night' => 30,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                'max_guests' => 1,
                'min_nights' => 1,
                'max_nights' => null,
                ...$placeOverrides,
            ]);

        $place->translations()->create(['locale' => 'en', 'title' => $title, 'summary' => 'Summary '.$title]);
        $place->translations()->create(['locale' => 'ru', 'title' => 'RU '.$title, 'summary' => 'RU Summary '.$title]);

        MediaItem::factory()->create([
            'owner_type' => SleepingPlace::class,
            'owner_id' => $place->id,
            'mediable_type' => SleepingPlace::class,
            'mediable_id' => $place->id,
            'owner_user_id' => $host->id,
            'path' => 'places/original.jpg',
            'mobile_path' => 'places/mobile.jpg',
            'full_path' => 'places/full.jpg',
        ]);

        return $place;
    }

    private function amenity(string $slug): Amenity
    {
        $amenity = Amenity::factory()->create([
            'slug' => $slug,
            'name_normalized' => $slug,
            'category' => 'comfort',
            'status' => 'active',
        ]);

        $label = str($slug)->replace('_', ' ')->title()->toString();
        $amenity->translations()->create(['locale' => 'en', 'name' => $label, 'name_normalized' => str($label)->lower()->toString()]);
        $amenity->translations()->create(['locale' => 'ru', 'name' => 'RU '.$label, 'name_normalized' => 'ru '.str($label)->lower()->toString()]);

        return $amenity;
    }

    private function rule(string $slug): Rule
    {
        $rule = Rule::factory()->create([
            'slug' => $slug,
            'name_normalized' => $slug,
            'category' => 'house',
            'status' => 'active',
        ]);

        $label = str($slug)->replace('_', ' ')->title()->toString();
        $rule->translations()->create(['locale' => 'en', 'name' => $label, 'name_normalized' => str($label)->lower()->toString()]);
        $rule->translations()->create(['locale' => 'ru', 'name' => 'RU '.$label, 'name_normalized' => 'ru '.str($label)->lower()->toString()]);

        return $rule;
    }
}
