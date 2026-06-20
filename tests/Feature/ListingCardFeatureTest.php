<?php

namespace Tests\Feature;

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
use App\Models\City;
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\User;
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
