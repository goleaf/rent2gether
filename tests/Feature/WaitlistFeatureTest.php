<?php

namespace Tests\Feature;

use App\Data\Waitlist\WaitlistContext;
use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Waitlist\HostWaitingGuestsPanel;
use App\Livewire\Waitlist\JoinWaitlistButton;
use App\Livewire\Waitlist\JoinWaitlistSheet;
use App\Livewire\Waitlist\MyWaitlistPage;
use App\Livewire\Waitlist\WaitlistOfferPage;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;
use App\Services\Waitlist\WaitlistAvailabilityService;
use App\Services\Waitlist\WaitlistOfferService;
use App\Services\Waitlist\WaitlistQueueService;
use App\Services\Waitlist\WaitlistService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WaitlistFeatureTest extends TestCase
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

    public function test_guest_can_join_update_pause_resume_and_cancel_waitlist(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Queue bed', ['base_price_per_night' => 25, 'deposit_amount' => 15]);

        $result = app(WaitlistService::class)->join($guest, $place, $this->context());

        $this->assertFalse($result->alreadyJoined);
        $this->assertSame(1, $result->position);
        $this->assertDatabaseHas('waitlist_items', [
            'id' => $result->item->id,
            'user_id' => $guest->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'status' => 'active',
            'desired_check_in_date' => '2026-07-10',
            'desired_check_out_date' => '2026-07-12',
            'nights_count' => 2,
            'calendar_days_count' => 3,
            'guests_count' => 1,
            'max_price_per_night' => 35,
            'max_total_price' => 120,
            'max_deposit' => 20,
            'ready_to_book_immediately' => true,
            'auto_send_request' => true,
            'notify_available' => true,
        ]);

        $again = app(WaitlistService::class)->join($guest, $place, $this->context(maxTotalPrice: 150));

        $this->assertTrue($again->alreadyJoined);
        $this->assertSame($result->item->id, $again->item->id);
        $this->assertSame(1, WaitlistItem::query()->count());

        $updated = app(WaitlistService::class)->update($guest, $result->item, [
            'max_total_price' => 150,
            'guest_message' => 'I can arrive quickly.',
        ]);

        $this->assertSame('150.00', $updated->max_total_price);

        app(WaitlistService::class)->pause($guest, $updated);
        $this->assertDatabaseHas('waitlist_items', ['id' => $updated->id, 'status' => 'paused']);

        app(WaitlistService::class)->resume($guest, $updated->fresh());
        $this->assertDatabaseHas('waitlist_items', ['id' => $updated->id, 'status' => 'active']);

        app(WaitlistService::class)->cancel($guest, $updated->fresh());
        $this->assertDatabaseHas('waitlist_items', [
            'id' => $updated->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_queue_selects_first_eligible_guest_and_skips_to_next(): void
    {
        $place = $this->createPlace('Popular place', ['base_price_per_night' => 20, 'deposit_amount' => 10]);
        $first = User::factory()->create();
        $second = User::factory()->create();

        $firstItem = app(WaitlistService::class)->join($first, $place, $this->context())->item;
        $secondItem = app(WaitlistService::class)->join($second, $place, $this->context())->item;

        $this->assertSame(1, app(WaitlistQueueService::class)->calculatePosition($firstItem->fresh()));
        $this->assertSame(2, app(WaitlistQueueService::class)->calculatePosition($secondItem->fresh()));

        $offer = app(WaitlistAvailabilityService::class)->handlePlaceBecameAvailable(
            $place,
            CarbonImmutable::parse('2026-07-10'),
            CarbonImmutable::parse('2026-07-12'),
        );

        $this->assertInstanceOf(WaitlistOffer::class, $offer);
        $this->assertSame($first->id, $offer->user_id);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $first->id,
            'type' => 'waitlist_offer_created',
            'title_key' => 'notifications.waitlist_offer_created.title',
        ]);

        $nextOffer = app(WaitlistOfferService::class)->decline($first, $offer);

        $this->assertSame('declined', $offer->fresh()->status);
        $this->assertInstanceOf(WaitlistOffer::class, $nextOffer);
        $this->assertSame($second->id, $nextOffer->user_id);
        $this->assertDatabaseHas('waitlist_items', [
            'id' => $firstItem->id,
            'skipped_count' => 1,
        ]);
    }

    public function test_eligibility_rejects_high_price_unavailable_expired_and_skipped_items(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Eligibility place', ['base_price_per_night' => 50, 'deposit_amount' => 100]);
        $queue = app(WaitlistQueueService::class);

        $highPrice = app(WaitlistService::class)->join($guest, $place, $this->context(maxPricePerNight: 30, maxDeposit: 50))->item;
        $priceData = $queue->priceDataFor($highPrice->fresh());

        $this->assertFalse($queue->isEligible($highPrice->fresh(), $place, $priceData)->eligible);
        $this->assertContains('price_too_high', $queue->isEligible($highPrice->fresh(), $place, $priceData)->reasons);

        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-10',
            'status' => AvailabilityStatus::Occupied,
        ]);

        $unavailable = app(WaitlistService::class)->join(User::factory()->create(), $place, $this->context())->item;
        $unavailableResult = app(WaitlistAvailabilityService::class)->checkItem($unavailable->fresh());

        $this->assertFalse($unavailableResult->eligible);
        $this->assertContains('unavailable', $unavailableResult->reasons);

        $expired = WaitlistItem::factory()->create([
            'user_id' => User::factory(),
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'status' => 'active',
            'desired_check_in' => '2026-06-10',
            'desired_check_out' => '2026-06-12',
            'desired_check_in_date' => '2026-06-10',
            'desired_check_out_date' => '2026-06-12',
            'skipped_count' => 3,
            'max_skips' => 3,
        ]);

        $expiredResult = $queue->isEligible($expired->fresh(), $place, $queue->priceDataFor($expired->fresh()));

        $this->assertFalse($expiredResult->eligible);
        $this->assertContains('dates_expired', $expiredResult->reasons);
        $this->assertContains('too_many_skips', $expiredResult->reasons);
    }

    public function test_accepting_offer_creates_booking_without_automatic_payment(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Instant waitlist place', [
            'base_price_per_night' => 20,
            'deposit_amount' => 0,
            'instant_booking_enabled' => true,
            'requires_host_approval' => false,
        ]);
        $item = app(WaitlistService::class)->join($guest, $place, $this->context())->item;
        $offer = app(WaitlistOfferService::class)->createOffer($item->fresh(), app(WaitlistQueueService::class)->priceDataFor($item->fresh()));

        $booking = app(WaitlistOfferService::class)->accept($guest, $offer);

        $this->assertSame($guest->id, $booking->guest_user_id);
        $this->assertSame($place->id, $booking->sleeping_place_id);
        $this->assertSame(BookingStatus::AwaitingPayment, $booking->status);
        $this->assertSame(PaymentStatus::Unpaid, $booking->payment_status);
        $this->assertDatabaseHas('waitlist_offers', [
            'id' => $offer->id,
            'status' => 'converted_to_booking',
            'booking_id' => $booking->id,
        ]);
        $this->assertDatabaseHas('waitlist_items', [
            'id' => $item->id,
            'status' => 'completed',
        ]);
    }

    public function test_booking_cancellation_triggers_first_waitlist_offer(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Cancelled booking place', ['base_price_per_night' => 20, 'deposit_amount' => 0]);
        $booking = Booking::factory()->for($place)->create([
            'sleeping_place_id' => $place->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'guest_user_id' => User::factory(),
            'host_user_id' => $place->property->host_user_id,
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-12',
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-12',
        ]);
        app(WaitlistService::class)->join($guest, $place, $this->context());

        $offer = app(WaitlistAvailabilityService::class)->handleBookingCancelled($booking);

        $this->assertInstanceOf(WaitlistOffer::class, $offer);
        $this->assertSame($guest->id, $offer->user_id);
    }

    public function test_livewire_waitlist_pages_and_host_panel_work(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Livewire waitlist place', ['base_price_per_night' => 20, 'deposit_amount' => 0]);

        Livewire::actingAs($guest)
            ->test(JoinWaitlistButton::class, [
                'sleepingPlaceId' => $place->id,
                'checkIn' => '2026-07-10',
                'checkOut' => '2026-07-12',
                'guestsCount' => 1,
            ])
            ->assertSee(__('waitlist.join'))
            ->call('join')
            ->assertHasNoErrors()
            ->assertSee(__('waitlist.joined'));

        Livewire::actingAs(User::factory()->create())
            ->test(JoinWaitlistSheet::class, ['sleepingPlaceId' => $place->id])
            ->set('desiredCheckIn', '2026-07-10')
            ->set('desiredCheckOut', '2026-07-12')
            ->set('maxPricePerNight', '30')
            ->call('join')
            ->assertHasNoErrors();

        $offer = app(WaitlistAvailabilityService::class)->handlePlaceBecameAvailable(
            $place,
            CarbonImmutable::parse('2026-07-10'),
            CarbonImmutable::parse('2026-07-12'),
        );

        Livewire::actingAs($guest)
            ->test(MyWaitlistPage::class)
            ->assertSee(__('waitlist.my_waitlist'))
            ->assertSee('Livewire waitlist place');

        Livewire::actingAs($guest)
            ->test(WaitlistOfferPage::class, ['waitlistOffer' => $offer])
            ->assertSee(__('waitlist.offer_available'))
            ->call('decline')
            ->assertHasNoErrors();

        Livewire::actingAs($place->property->host)
            ->test(HostWaitingGuestsPanel::class, ['sleepingPlaceId' => $place->id])
            ->assertSee(__('waitlist.host.title'))
            ->assertSee(__('waitlist.host.waiting_count', ['count' => 2]));

        Livewire::actingAs(User::factory()->create(['is_host' => true]))
            ->test(HostWaitingGuestsPanel::class, ['sleepingPlaceId' => $place->id])
            ->assertForbidden();
    }

    public function test_waitlist_offer_page_keeps_offer_model_out_of_public_state(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Offer payload place', ['base_price_per_night' => 20, 'deposit_amount' => 0]);

        app(WaitlistService::class)->join($guest, $place, $this->context());
        $offer = app(WaitlistAvailabilityService::class)->handlePlaceBecameAvailable(
            $place,
            CarbonImmutable::parse('2026-07-10'),
            CarbonImmutable::parse('2026-07-12'),
        );

        $component = Livewire::actingAs($guest)
            ->test(WaitlistOfferPage::class, ['waitlistOffer' => $offer])
            ->assertSet('waitlistOfferId', $offer->id)
            ->assertViewHas('offer', fn (WaitlistOffer $viewOffer): bool => $viewOffer->is($offer))
            ->assertViewHas('item', fn (?WaitlistItem $item): bool => $item?->is($offer->waitlistItem) === true)
            ->assertSee(__('waitlist.offer_available'));

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('waitlistOfferId', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\WaitlistOffer', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\WaitlistItem', $encodedSnapshot);
        $this->assertLessThan(13_000, strlen($encodedSnapshot), 'Waitlist offer page snapshot should keep full offer models out of public state.');
    }

    public function test_waitlist_routes_render_in_english_and_russian(): void
    {
        $guest = User::factory()->create();

        foreach (['en', 'ru'] as $locale) {
            $this->actingAs($guest)
                ->get(route('waitlist.index', ['locale' => $locale]))
                ->assertOk()
                ->assertSee(__('waitlist.my_waitlist', [], $locale))
                ->assertSee(__('waitlist.empty.title', [], $locale));
        }
    }

    private function context(
        ?float $maxPricePerNight = 35,
        ?float $maxTotalPrice = 120,
        ?float $maxDeposit = 20,
    ): WaitlistContext {
        return new WaitlistContext(
            desiredCheckIn: '2026-07-10',
            desiredCheckOut: '2026-07-12',
            guestsCount: 1,
            maxPricePerNight: $maxPricePerNight,
            maxTotalPrice: $maxTotalPrice,
            maxDeposit: $maxDeposit,
            source: 'test',
            readyToBookImmediately: true,
            autoSendRequest: true,
            notifyAvailable: true,
            notifyPriceDrop: true,
            guestMessage: 'I am ready if it opens.',
            expiresAt: '2026-08-01 10:00:00',
        );
    }

    /**
     * @param  array<string, mixed>  $placeOverrides
     */
    private function createPlace(string $title, array $placeOverrides = []): SleepingPlace
    {
        $city = $this->city('Vilnius');
        $host = User::factory()->create(['is_host' => true]);
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
            ]);
        $room = Room::factory()
            ->for($property)
            ->create([
                'status' => RoomStatus::Active,
                'type' => RoomType::Shared,
            ]);
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'base_price_per_night' => 30,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                'max_guests' => 1,
                'min_nights' => 1,
                ...$placeOverrides,
            ]);

        $place->translations()->create([
            'locale' => 'en',
            'title' => $title,
            'summary' => $title,
            'description' => $title,
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'RU '.$title,
            'summary' => 'RU '.$title,
            'description' => 'RU '.$title,
        ]);

        return $place->fresh(['property.host']);
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
