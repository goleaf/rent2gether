<?php

namespace Tests\Feature;

use App\Data\Hints\HintContext;
use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Hints\BeforeBookingHints;
use App\Livewire\Hints\DismissHintButton;
use App\Livewire\Hints\ListingCardHints;
use App\Livewire\Hints\ListingDetailHints;
use App\Models\City;
use App\Models\Country;
use App\Models\GuestCompatibilityProfile;
use App\Models\GuestCompatibilityVisibilitySetting;
use App\Models\GuestHintDismissal;
use App\Models\GuestHintImpression;
use App\Models\ListingHintSnapshot;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\RoomOccupantSnapshot;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Hints\GuestHintService;
use App\Services\Hints\HintDismissalService;
use App\Services\Hints\ListingHintCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AutomaticGuestHintsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_hint_tables_relationships_indexes_and_cascade_delete_exist(): void
    {
        $this->assertTrue(Schema::hasTable('listing_hint_snapshots'));
        $this->assertTrue(Schema::hasTable('guest_hint_dismissals'));
        $this->assertTrue(Schema::hasTable('guest_hint_impressions'));
        $this->assertTrue(Schema::hasColumn('listing_hint_snapshots', 'message_params_json'));
        $this->assertTrue(Schema::hasColumn('guest_hint_dismissals', 'dismissed_at'));
        $this->assertTrue(Schema::hasColumn('guest_hint_impressions', 'shown_at'));
        $this->assertTrue(Schema::hasIndex('listing_hint_snapshots', ['sleeping_place_id', 'category']));
        $this->assertTrue(Schema::hasIndex('listing_hint_snapshots', ['show_before_booking']));
        $this->assertTrue(Schema::hasIndex('guest_hint_dismissals', ['user_id', 'hint_key']));
        $this->assertTrue(Schema::hasIndex('guest_hint_impressions', ['sleeping_place_id', 'hint_key']));

        $guest = User::factory()->create();
        $place = $this->place();
        $snapshot = ListingHintSnapshot::factory()
            ->for($place)
            ->for($place->property)
            ->for($place->room)
            ->for($place->property->cityModel, 'city')
            ->create();
        $dismissal = GuestHintDismissal::factory()
            ->for($guest, 'user')
            ->for($place)
            ->create();
        $impression = GuestHintImpression::factory()
            ->for($guest, 'user')
            ->for($place)
            ->create();

        $this->assertSame($place->id, $snapshot->sleepingPlace->id);
        $this->assertSame($place->room_id, $snapshot->room->id);
        $this->assertSame($guest->id, $dismissal->user->id);
        $this->assertSame($place->id, $impression->sleepingPlace->id);

        $guest->delete();
        $place->delete();

        $this->assertDatabaseMissing('listing_hint_snapshots', ['id' => $snapshot->id]);
        $this->assertDatabaseMissing('guest_hint_dismissals', ['id' => $dismissal->id]);
        $this->assertDatabaseMissing('guest_hint_impressions', ['id' => $impression->id]);
    }

    public function test_calculator_builds_honest_hints_from_real_listing_context(): void
    {
        $guest = User::factory()->create();
        GuestCompatibilityProfile::factory()->for($guest, 'user')->create([
            'avoids_upper_bunk' => true,
            'needs_locker' => true,
        ]);
        GuestCompatibilityVisibilitySetting::factory()->for($guest, 'user')->create();

        $place = $this->place(
            roomOverrides: [
                'available_places_count' => 1,
                'sleeping_places_count' => 4,
                'current_guests_count' => 3,
                'can_talk_at_night' => false,
                'noise_level' => 'quiet',
            ],
            placeOverrides: [
                'base_price_per_night' => 20,
                'weekly_price' => 120,
                'monthly_price' => 420,
                'weekend_price' => 30,
                'deposit_amount' => 50,
                'instant_booking_enabled' => true,
                'requires_host_approval' => false,
                'is_top_bunk' => true,
                'type' => SleepingPlaceType::BunkTop->value,
                'sleeping_place_type' => SleepingPlaceType::BunkTop->value,
                'has_locker' => false,
            ],
            propertyOverrides: [
                'show_exact_address_before_booking' => false,
                'cleanliness_level' => 'excellent',
                'safety_level' => 'excellent',
            ],
        );
        $this->place(
            city: $place->property->cityModel,
            placeOverrides: ['base_price_per_night' => 80],
        );
        RoomOccupantSnapshot::factory()
            ->for($place->room)
            ->for($place)
            ->create([
                'check_in_date' => '2026-07-09',
                'check_out_date' => '2026-07-20',
            ]);

        $hints = app(ListingHintCalculatorService::class)->calculateDynamicHints(
            $place,
            new HintContext(
                checkInDate: '2026-07-10',
                checkOutDate: '2026-07-17',
                nightsCount: 7,
                userId: $guest->id,
                locale: 'en',
            ),
        );
        $keys = $hints->pluck('key')->all();

        $this->assertContains('cheaper_than_area_average', $keys);
        $this->assertContains('one_place_left', $keys);
        $this->assertContains('people_already_in_room', $keys);
        $this->assertContains('weekly_discount', $keys);
        $this->assertContains('deposit_required', $keys);
        $this->assertContains('address_after_booking', $keys);
        $this->assertContains('strict_quiet_hours', $keys);
        $this->assertContains('criteria_mismatch', $keys);
        $this->assertContains('instant_booking', $keys);
    }

    public function test_guest_hint_service_filters_expired_and_dismissed_hints_but_keeps_critical_booking_warnings(): void
    {
        $guest = User::factory()->create();
        $place = $this->place(placeOverrides: ['deposit_amount' => 40]);
        $context = new HintContext(
            checkInDate: '2026-07-10',
            checkOutDate: '2026-07-13',
            nightsCount: 3,
            userId: $guest->id,
            locale: 'en',
        );

        ListingHintSnapshot::factory()->forPlace($place)->create([
            'hint_key' => 'host_responds_fast',
            'category' => 'host',
            'type' => 'positive',
            'importance' => 'medium',
            'priority' => 70,
            'message_key' => 'guest_hints.messages.host_responds_fast',
            'show_on_card' => true,
            'show_on_detail' => true,
            'expires_at' => now()->addDay(),
        ]);
        ListingHintSnapshot::factory()->forPlace($place)->create([
            'hint_key' => 'expired_hint',
            'category' => 'trust',
            'type' => 'info',
            'importance' => 'low',
            'priority' => 100,
            'message_key' => 'guest_hints.messages.new_listing',
            'show_on_card' => true,
            'expires_at' => now()->subMinute(),
        ]);

        app(HintDismissalService::class)->dismiss($guest, 'host_responds_fast', $place, 'card');
        app(HintDismissalService::class)->dismiss($guest, 'deposit_required', $place, 'before_booking');

        $cardKeys = app(GuestHintService::class)
            ->getHintsForCard($guest, $place, $context)
            ->pluck('key')
            ->all();
        $bookingKeys = app(GuestHintService::class)
            ->getHintsBeforeBooking($guest, $place, $context)
            ->pluck('key')
            ->all();

        $this->assertNotContains('host_responds_fast', $cardKeys);
        $this->assertNotContains('expired_hint', $cardKeys);
        $this->assertContains('deposit_required', $bookingKeys);
    }

    public function test_card_detail_and_before_booking_livewire_components_render_localized_hints(): void
    {
        $guest = User::factory()->create();
        $place = $this->place(placeOverrides: ['deposit_amount' => 40]);

        ListingHintSnapshot::factory()->forPlace($place)->create([
            'hint_key' => 'high_cleanliness_rating',
            'category' => 'trust',
            'type' => 'positive',
            'importance' => 'medium',
            'priority' => 80,
            'message_key' => 'guest_hints.messages.high_cleanliness_rating',
            'show_on_card' => true,
            'show_on_detail' => true,
        ]);

        Livewire::actingAs($guest)
            ->test(ListingCardHints::class, [
                'sleepingPlaceId' => $place->id,
                'checkIn' => '2026-07-10',
                'checkOut' => '2026-07-13',
            ])
            ->assertSee(__('guest_hints.messages.high_cleanliness_rating'));

        Livewire::actingAs($guest)
            ->test(ListingDetailHints::class, [
                'sleepingPlaceId' => $place->id,
                'checkIn' => '2026-07-10',
                'checkOut' => '2026-07-13',
            ])
            ->assertSee(__('guest_hints.important_to_know'))
            ->assertSee(__('guest_hints.categories.trust'))
            ->assertDontSeeLivewire(DismissHintButton::class);

        Livewire::actingAs($guest)
            ->test(BeforeBookingHints::class, [
                'sleepingPlaceId' => $place->id,
                'checkIn' => '2026-07-10',
                'checkOut' => '2026-07-13',
            ])
            ->assertSee(__('guest_hints.before_booking'))
            ->assertSee(__('guest_hints.messages.deposit_required'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(ListingCardHints::class, [
                'sleepingPlaceId' => $place->id,
                'checkIn' => '2026-07-10',
                'checkOut' => '2026-07-13',
            ])
            ->assertSee(__('guest_hints.messages.high_cleanliness_rating', [], 'ru'));
    }

    public function test_dismiss_button_hides_non_critical_hint_and_refuses_critical_before_booking_hint(): void
    {
        $guest = User::factory()->create();
        $place = $this->place();

        Livewire::actingAs($guest)
            ->test(DismissHintButton::class, [
                'sleepingPlaceId' => $place->id,
                'hintKey' => 'high_cleanliness_rating',
                'context' => 'card',
                'critical' => false,
            ])
            ->call('dismiss')
            ->assertDispatched('guest-hint-dismissed');

        $this->assertDatabaseHas('guest_hint_dismissals', [
            'user_id' => $guest->id,
            'sleeping_place_id' => $place->id,
            'hint_key' => 'high_cleanliness_rating',
        ]);

        Livewire::actingAs($guest)
            ->test(DismissHintButton::class, [
                'sleepingPlaceId' => $place->id,
                'hintKey' => 'deposit_required',
                'context' => 'before_booking',
                'critical' => true,
            ])
            ->call('dismiss')
            ->assertHasErrors(['hint']);
    }

    /**
     * @param  array<string, mixed>  $roomOverrides
     * @param  array<string, mixed>  $placeOverrides
     * @param  array<string, mixed>  $propertyOverrides
     */
    private function place(
        array $roomOverrides = [],
        array $placeOverrides = [],
        array $propertyOverrides = [],
        ?City $city = null,
    ): SleepingPlace {
        $city ??= $this->city('Vilnius');
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create(array_merge([
                'user_id' => $host->id,
                'host_user_id' => $host->id,
                'country_id' => $city->country_id,
                'region_id' => $city->region_id,
                'city_id' => $city->id,
                'city' => $city->name,
                'district' => 'Old Town',
                'status' => PropertyStatus::Active,
                'amenities' => ['wifi', 'fast_wifi'],
                'rules' => ['quiet_hours', 'no_smoking'],
            ], $propertyOverrides));
        $room = Room::factory()
            ->for($property)
            ->create(array_merge([
                'status' => RoomStatus::Active,
                'gender_policy' => GenderType::Mixed->value,
                'sleeping_places_count' => 4,
                'available_places_count' => 2,
                'current_guests_count' => 1,
                'noise_level' => 'quiet',
                'can_talk_at_night' => false,
            ], $roomOverrides));

        return SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create(array_merge([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::Single->value,
                'sleeping_place_type' => SleepingPlaceType::Single->value,
                'base_price_per_night' => 30,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => 30,
            ], $placeOverrides));
    }

    private function city(string $name): City
    {
        $country = Country::factory()->create(['name_en' => 'Lithuania']);
        $region = Region::factory()->for($country)->create(['name' => $name.' County']);

        return City::factory()
            ->for($country)
            ->for($region)
            ->create([
                'name' => $name,
                'ascii_name' => $name,
                'population' => 500000,
                'status' => City::STATUS_ACTIVE,
                'is_active' => true,
            ]);
    }
}
