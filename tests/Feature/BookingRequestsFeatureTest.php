<?php

namespace Tests\Feature;

use App\Livewire\Bookings\Requests\GuestBookingRequestPage;
use App\Livewire\Host\BookingRequests\HostBookingRequestDetailsSheet;
use App\Livewire\Host\BookingRequests\HostBookingRequestsPage;
use App\Models\BookingQuote;
use App\Models\BookingRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\User;
use App\Models\UserDocument;
use App\Services\BookingRequests\BookingRequestConversionService;
use App\Services\BookingRequests\BookingRequestCreationService;
use App\Services\BookingRequests\BookingRequestExpirationService;
use App\Services\BookingRequests\BookingRequestGuestResponseService;
use App\Services\BookingRequests\BookingRequestHostResponseService;
use App\Services\BookingRequests\BookingRequestNumberService;
use App\Services\BookingRequests\BookingRequestPrivacyService;
use App\Services\Bookings\BookingPriceQuoteService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BookingRequestsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_number_is_generated(): void
    {
        $number = app(BookingRequestNumberService::class)->generate();

        $this->assertMatchesRegularExpression('/^BR-\d{4}-\d{6}$/', $number);
    }

    public function test_guest_can_create_host_approval_request_from_valid_quote_with_hold_snapshots_warnings_and_compatibility(): void
    {
        [$guest, $host, $place] = $this->placeSetup();
        $guest->forceFill([
            'identity_verified' => false,
            'phone_verified' => false,
            'is_smoker' => true,
            'has_pets' => true,
        ])->save();
        $place->property->forceFill(['rules' => ['smoking' => false, 'pets' => false]])->save();
        $checkIn = CarbonImmutable::now()->addDay()->startOfDay();
        $quote = $this->quoteFor($guest, $host, $place, [
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkIn->addDays(3)->toDateString(),
        ]);

        $request = app(BookingRequestCreationService::class)->createFromQuote($guest, $quote, [
            'request_type' => BookingRequest::TYPE_HOST_APPROVAL,
            'trip_purpose' => 'work',
            'planned_arrival_time' => '23:30',
            'planned_departure_time' => '05:30',
            'guest_message' => 'booking_requests.demo.guest_message',
            'needs_early_check_in' => true,
            'needs_late_checkout' => true,
            'hold_dates' => true,
            'guest_agreed_to_rules' => true,
            'guest_agreed_to_cancellation_policy' => true,
            'guest_agreed_to_deposit_policy' => true,
        ]);

        $this->assertSame($quote->id, $request->booking_quote_id);
        $this->assertSame($guest->id, $request->guest_user_id);
        $this->assertSame($host->id, $request->host_user_id);
        $this->assertSame($place->id, $request->sleeping_place_id);
        $this->assertSame($place->room_id, $request->room_id);
        $this->assertSame($place->property_id, $request->property_id);
        $this->assertNotEmpty($request->price_snapshot_json);
        $this->assertNotEmpty($request->guest_profile_snapshot_json);
        $this->assertTrue($request->warnings()->whereIn('warning_key', [
            'late_night_arrival',
            'very_early_checkout',
            'identity_not_verified',
            'phone_not_verified',
            'no_reviews',
            'last_minute_request',
            'smoking_conflict',
            'pet_conflict',
            'early_check_in_requested',
            'late_checkout_requested',
        ])->exists());
        $this->assertTrue($request->compatibilityResults()->where('compatibility_key', 'guest_count')->exists());
        $this->assertSame(3, $request->dateLocks()->where('status', 'active')->where('lock_type', 'host_confirmation_pending')->count());
        $this->assertTrue(Notification::query()->where('user_id', $host->id)->where('type', 'booking_request_new')->exists());
    }

    public function test_expired_quote_is_rejected_and_other_request_types_can_be_created(): void
    {
        [$guest, $host, $place] = $this->placeSetup();
        $expiredQuote = $this->quoteFor($guest, $host, $place, ['expires_at' => now()->subMinute()]);

        $this->expectException(ValidationException::class);

        try {
            app(BookingRequestCreationService::class)->createFromQuote($guest, $expiredQuote, [
                'request_type' => BookingRequest::TYPE_HOST_APPROVAL,
            ]);
        } finally {
            $preliminary = app(BookingRequestCreationService::class)->createPreliminaryInquiry($guest, $place, [
                'check_in_date' => '2026-07-20',
                'check_out_date' => '2026-07-23',
                'guests_count' => 1,
                'guest_message' => 'booking_requests.demo.guest_message',
            ]);

            $this->assertSame(BookingRequest::TYPE_PRELIMINARY_INQUIRY, $preliminary->request_type);
            $this->assertFalse($preliminary->hold_dates);
            $this->assertSame(0, $preliminary->dateLocks()->count());

            $longTerm = app(BookingRequestCreationService::class)->createLongTermRequest($guest, $this->quoteFor($guest, $host, $place, [
                'check_in_date' => '2026-08-01',
                'check_out_date' => '2026-09-05',
                'nights_count' => 35,
                'chargeable_days_count' => 35,
                'calendar_presence_days_count' => 36,
            ]), []);
            $urgent = app(BookingRequestCreationService::class)->createSameDayUrgentRequest($guest, $this->quoteFor($guest, $host, $place, [
                'check_in_date' => CarbonImmutable::now()->addDays(30)->toDateString(),
                'check_out_date' => CarbonImmutable::now()->addDays(32)->toDateString(),
                'nights_count' => 2,
                'chargeable_days_count' => 2,
                'calendar_presence_days_count' => 3,
            ]), []);

            $this->assertSame(BookingRequest::TYPE_LONG_TERM_REQUEST, $longTerm->request_type);
            $this->assertSame(BookingRequest::TYPE_SAME_DAY_URGENT, $urgent->request_type);
            $this->assertTrue($urgent->hold_dates);
        }
    }

    public function test_host_privacy_responses_guest_responses_and_hold_release_work(): void
    {
        [$guest, $host, $place] = $this->placeSetup();
        UserDocument::factory()->create(['user_id' => $guest->id]);
        $request = app(BookingRequestCreationService::class)->createFromQuote($guest, $this->quoteFor($guest, $host, $place), [
            'request_type' => BookingRequest::TYPE_HOST_APPROVAL,
            'hold_dates' => true,
        ]);
        $otherHost = User::factory()->host()->create();
        $privacy = app(BookingRequestPrivacyService::class);

        $this->assertTrue($privacy->canHostView($host, $request));
        $this->assertFalse($privacy->canHostView($otherHost, $request));
        $this->assertArrayNotHasKey('documents', $privacy->filterForHost($host, $request)['guest_profile']);

        app(BookingRequestHostResponseService::class)->askQuestion($host, $request, 'booking_requests.demo.host_response');
        $this->assertSame(BookingRequest::STATUS_WAITING_GUEST_RESPONSE, $request->fresh()->status);

        app(BookingRequestGuestResponseService::class)->answerQuestion($guest, $request->fresh(), 'booking_requests.demo.guest_response');
        $this->assertSame(BookingRequest::STATUS_WAITING_HOST_RESPONSE, $request->fresh()->status);

        app(BookingRequestHostResponseService::class)->approve($host, $request->fresh());
        $this->assertSame(BookingRequest::STATUS_APPROVED, $request->fresh()->status);
        $this->assertTrue(Notification::query()->where('user_id', $guest->id)->where('type', 'booking_request_approved')->exists());

        $rejectable = app(BookingRequestCreationService::class)->createFromQuote($guest, $this->quoteFor($guest, $host, $place, [
            'check_in_date' => '2026-10-10',
            'check_out_date' => '2026-10-13',
        ]), [
            'request_type' => BookingRequest::TYPE_HOST_APPROVAL,
            'hold_dates' => true,
        ]);

        app(BookingRequestHostResponseService::class)->reject($host, $rejectable, 'other');
        $this->assertSame(BookingRequest::STATUS_REJECTED, $rejectable->fresh()->status);
        $this->assertSame(0, $rejectable->dateLocks()->where('status', 'active')->count());
        $this->assertTrue(Notification::query()->where('user_id', $guest->id)->where('type', 'booking_request_rejected')->exists());

        $withdrawable = app(BookingRequestCreationService::class)->createFromQuote($guest, $this->quoteFor($guest, $host, $place, [
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-13',
        ]), ['hold_dates' => true]);
        app(BookingRequestGuestResponseService::class)->withdrawRequest($guest, $withdrawable);

        $this->assertSame(BookingRequest::STATUS_WITHDRAWN_BY_GUEST, $withdrawable->fresh()->status);
        $this->assertSame(0, $withdrawable->dateLocks()->where('status', 'active')->count());
    }

    public function test_expiration_releases_holds_and_notifies_guest(): void
    {
        [$guest, $host, $place] = $this->placeSetup();
        $request = app(BookingRequestCreationService::class)->createFromQuote($guest, $this->quoteFor($guest, $host, $place), [
            'hold_dates' => true,
            'expires_at' => now()->subMinute(),
        ]);

        app(BookingRequestExpirationService::class)->expireRequest($request);

        $this->assertSame(BookingRequest::STATUS_EXPIRED, $request->fresh()->status);
        $this->assertSame(0, $request->dateLocks()->where('status', 'active')->count());
        $this->assertTrue(Notification::query()->where('user_id', $guest->id)->where('type', 'booking_request_expired')->exists());
    }

    public function test_approved_request_converts_to_booking_after_recheck_and_creates_locks(): void
    {
        [$guest, $host, $place] = $this->placeSetup(instant: true);
        $quote = app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'guests_count' => 1,
        ]);
        $request = app(BookingRequestCreationService::class)->createFromQuote($guest, $quote, [
            'request_type' => BookingRequest::TYPE_HOST_APPROVAL,
            'hold_dates' => true,
        ]);
        app(BookingRequestHostResponseService::class)->approve($host, $request);

        $booking = app(BookingRequestConversionService::class)->convertApprovedRequestToBooking($request->fresh());

        $this->assertSame($booking->id, $request->fresh()->booking_id);
        $this->assertSame(BookingRequest::STATUS_CONVERTED_TO_BOOKING, $request->fresh()->status);
        $this->assertSame($place->id, $booking->sleeping_place_id);
        $this->assertTrue($booking->priceSnapshot()->exists());
        $this->assertSame(3, SleepingPlaceBookingDateLock::query()->where('booking_id', $booking->id)->where('status', 'active')->count());
        $this->assertSame(0, SleepingPlaceBookingDateLock::query()->where('booking_request_id', $request->id)->where('status', 'active')->count());
    }

    public function test_livewire_request_pages_render_in_english_and_russian(): void
    {
        [$guest, $host, $place] = $this->placeSetup();
        $request = app(BookingRequestCreationService::class)->createFromQuote($guest, $this->quoteFor($guest, $host, $place), [
            'request_type' => BookingRequest::TYPE_HOST_APPROVAL,
            'hold_dates' => false,
        ]);

        app()->setLocale('en');

        Livewire::actingAs($guest)
            ->test(GuestBookingRequestPage::class, ['request' => $request->id])
            ->assertSee(__('booking_requests.guest_page.title', [], 'en'));

        app()->setLocale('ru');

        Livewire::actingAs($host)
            ->test(HostBookingRequestDetailsSheet::class, ['request' => $request->id])
            ->assertSee(__('booking_requests.host_details.title', [], 'ru'));
    }

    public function test_request_routes_use_new_guest_and_host_surfaces_with_owner_checks(): void
    {
        [$guest, $host, $place] = $this->placeSetup();
        $request = app(BookingRequestCreationService::class)->createFromQuote($guest, $this->quoteFor($guest, $host, $place), [
            'request_type' => BookingRequest::TYPE_HOST_APPROVAL,
            'hold_dates' => false,
        ]);

        $this->actingAs($guest)
            ->get(route('guest.booking-requests.show', [
                'locale' => 'en',
                'request' => $request,
            ]))
            ->assertOk()
            ->assertSeeLivewire(GuestBookingRequestPage::class)
            ->assertSee(__('booking_requests.guest_page.title', [], 'en'))
            ->assertSee($request->request_number);

        $this->actingAs(User::factory()->create())
            ->get(route('guest.booking-requests.show', [
                'locale' => 'en',
                'request' => $request,
            ]))
            ->assertForbidden();

        $this->actingAs($host)
            ->get(route('host.requests.index', ['locale' => 'ru']))
            ->assertOk()
            ->assertSeeLivewire(HostBookingRequestsPage::class)
            ->assertSee(__('booking_requests.host_page.title', [], 'ru'))
            ->assertSee($request->guest->name);
    }

    /**
     * @return array{0:User,1:User,2:SleepingPlace}
     */
    private function placeSetup(bool $instant = false): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $property = Property::factory()->create(['host_user_id' => $host->id, 'rules' => []]);
        $room = Room::factory()->for($property)->create(['user_id' => $host->id]);
        $place = SleepingPlace::factory()->for($property)->for($room)->create([
            'user_id' => $host->id,
            'base_price_per_night' => 20,
            'base_price' => 20,
            'cleaning_fee' => 10,
            'deposit_amount' => 50,
            'max_guests' => 1,
            'max_guests_count' => 1,
            'instant_booking_enabled' => $instant,
            'requires_host_approval' => ! $instant,
        ]);

        return [$guest, $host, $place];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function quoteFor(User $guest, User $host, SleepingPlace $place, array $overrides = []): BookingQuote
    {
        return BookingQuote::factory()->create([
            'user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => '2026-07-10',
            'check_in_time' => '15:00',
            'check_out_date' => '2026-07-13',
            'check_out_time' => '11:00',
            'nights_count' => 3,
            'chargeable_days_count' => 3,
            'calendar_presence_days_count' => 4,
            'availability_status' => 'available',
            'validation_status' => 'valid',
            'pricing_status' => 'calculated',
            'status' => BookingQuote::STATUS_VALID,
            'expires_at' => now()->addMinutes(20),
            ...$overrides,
        ]);
    }
}
