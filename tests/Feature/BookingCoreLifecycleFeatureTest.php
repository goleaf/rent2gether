<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Livewire\Bookings\Show\GuestBookingPage;
use App\Livewire\Host\Bookings\HostBookingsPage;
use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\BookingRequests\BookingRequestCreationService;
use App\Services\BookingRequests\BookingRequestHostResponseService;
use App\Services\Bookings\BookingCreationService;
use App\Services\Bookings\BookingGroupService;
use App\Services\Bookings\BookingHostApprovalService;
use App\Services\Bookings\BookingNumberService;
use App\Services\Bookings\BookingPaymentStateService;
use App\Services\Bookings\BookingPriceQuoteService;
use App\Services\Bookings\BookingPrivacyService;
use App\Services\Bookings\BookingRequirementService;
use App\Services\Bookings\BookingStatusService;
use App\Services\Bookings\BookingVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BookingCoreLifecycleFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_number_is_generated(): void
    {
        $number = app(BookingNumberService::class)->generate();

        $this->assertMatchesRegularExpression('/^BK-\d{4}-\d{6}$/', $number);
    }

    public function test_instant_booking_from_valid_quote_stores_core_context_locks_snapshots_requirements_and_logs(): void
    {
        [$guest, $host, $place] = $this->placeSetup(instant: true);
        $quote = $this->quoteFor($guest, $place, '2026-07-10', '2026-07-13');

        $booking = app(BookingCreationService::class)->createInstantBooking($guest, $quote, [
            'guest_agreed_to_rules' => true,
        ]);

        $this->assertMatchesRegularExpression('/^BK-\d{4}-\d{6}$/', $booking->booking_number);
        $this->assertSame($quote->id, $booking->booking_quote_id);
        $this->assertSame($guest->id, $booking->guest_user_id);
        $this->assertSame($host->id, $booking->host_user_id);
        $this->assertSame($place->id, $booking->sleeping_place_id);
        $this->assertSame($place->room_id, $booking->room_id);
        $this->assertSame($place->property_id, $booking->property_id);
        $this->assertSame(3, $booking->sleepingPlaceDateLocks()->where('status', 'active')->where('lock_type', 'booked')->count());
        $this->assertTrue($booking->priceSnapshot()->exists());
        $this->assertTrue($booking->requirements()->where('requirement_key', 'rules_acceptance')->exists());
        $this->assertTrue($booking->lifecycleEvents()->where('event_key', 'created')->exists());
        $this->assertTrue($booking->statusLogs()->where('new_status', BookingStatus::WaitingPayment->value)->exists());
        $this->assertTrue($booking->bookingGuests()->where('is_main_guest', true)->exists());
    }

    public function test_expired_or_stale_quote_cannot_create_booking(): void
    {
        [$guest, , $place] = $this->placeSetup(instant: true);
        $expired = $this->quoteFor($guest, $place, '2026-07-20', '2026-07-23');
        $expired->forceFill(['expires_at' => now()->subMinute()])->save();

        $this->expectException(ValidationException::class);

        app(BookingCreationService::class)->createInstantBooking($guest, $expired);
    }

    public function test_booking_one_sleeping_place_does_not_block_another_sleeping_place_in_same_room(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $property = Property::factory()->create(['host_user_id' => $host->id]);
        $room = Room::factory()->for($property)->create(['user_id' => $host->id]);
        $firstPlace = $this->sleepingPlaceFor($host, $property, $room, instant: true);
        $secondPlace = $this->sleepingPlaceFor($host, $property, $room, instant: true);

        $firstQuote = $this->quoteFor($guest, $firstPlace, '2026-08-10', '2026-08-13');
        $secondQuote = $this->quoteFor($guest, $secondPlace, '2026-08-10', '2026-08-13');

        $firstBooking = app(BookingCreationService::class)->createInstantBooking($guest, $firstQuote);
        $secondBooking = app(BookingCreationService::class)->createInstantBooking($guest, $secondQuote);

        $this->assertSame($firstPlace->id, $firstBooking->sleeping_place_id);
        $this->assertSame($secondPlace->id, $secondBooking->sleeping_place_id);
        $this->assertSame(3, $firstBooking->sleepingPlaceDateLocks()->where('status', 'active')->count());
        $this->assertSame(3, $secondBooking->sleepingPlaceDateLocks()->where('status', 'active')->count());
    }

    public function test_host_approval_request_converts_to_booking_after_approval(): void
    {
        [$guest, $host, $place] = $this->placeSetup(instant: true);
        $quote = $this->quoteFor($guest, $place, '2026-09-10', '2026-09-13');
        $request = app(BookingRequestCreationService::class)->createFromQuote($guest, $quote, [
            'request_type' => BookingRequest::TYPE_HOST_APPROVAL,
            'hold_dates' => true,
        ]);

        app(BookingRequestHostResponseService::class)->approve($host, $request);

        $booking = app(BookingCreationService::class)->createFromApprovedRequest($request->fresh());

        $this->assertSame($request->id, $booking->booking_request_id);
        $this->assertSame($booking->id, $request->fresh()->booking_id);
        $this->assertSame(BookingRequest::STATUS_CONVERTED_TO_BOOKING, $request->fresh()->status);
        $this->assertSame(3, $booking->sleepingPlaceDateLocks()->where('status', 'active')->where('lock_type', 'booked')->count());
    }

    public function test_payment_failure_releases_locks_and_host_approval_permissions_work(): void
    {
        [$guest, $host, $place] = $this->placeSetup(instant: false);
        $booking = app(BookingCreationService::class)->createHostApprovalBooking($guest, $this->quoteFor($guest, $place, '2026-10-10', '2026-10-13'));
        $otherHost = User::factory()->host()->create();

        $this->assertSame(3, $booking->sleepingPlaceDateLocks()->where('status', 'active')->count());
        $this->assertTrue(app(BookingPrivacyService::class)->canHostRespond($host, $booking));
        $this->assertFalse(app(BookingPrivacyService::class)->canHostRespond($otherHost, $booking));

        app(BookingHostApprovalService::class)->approve($host, $booking);
        $this->assertSame(BookingStatus::WaitingPayment, $booking->fresh()->status);

        app(BookingPaymentStateService::class)->markPaymentFailed($booking->fresh(), 'timeout');
        $this->assertSame(BookingStatus::PaymentFailed, $booking->fresh()->status);
        $this->assertSame(0, $booking->sleepingPlaceDateLocks()->where('status', 'active')->count());
    }

    public function test_verification_requirements_and_lifecycle_transitions_work(): void
    {
        $booking = $this->manualBooking(['status' => BookingStatus::Created->value]);

        $booking->forceFill([
            'requires_phone_verification' => true,
            'requires_identity_verification' => true,
            'requires_document_verification' => true,
            'verification_status' => 'pending',
        ])->save();

        $requirements = app(BookingRequirementService::class);
        $requirements->createRequirements($booking);
        $this->assertFalse($requirements->allRequiredCompleted($booking->fresh()));

        $verification = app(BookingVerificationService::class);
        $verification->markPhoneVerified($booking);
        $verification->markIdentityVerified($booking->fresh());
        $verification->markDocumentsVerified($booking->fresh());

        $this->assertSame('passed', $booking->fresh()->verification_status);

        $statuses = app(BookingStatusService::class);
        $booking = $statuses->transition($booking->fresh(), BookingStatus::WaitingPayment->value);
        $booking = $statuses->markPaid($booking);
        $booking = $statuses->markConfirmed($booking);
        $booking = $statuses->markReadyForCheckIn($booking);
        $booking = $statuses->markCheckedIn($booking);
        $booking = $statuses->markStayInProgress($booking);
        $booking = $statuses->markCheckOutSoon($booking);
        $booking = $statuses->markCheckedOut($booking);
        $booking = $statuses->markWaitingInspection($booking);
        $booking = $statuses->markWaitingDepositReturn($booking);
        $booking = $statuses->markWaitingReview($booking);
        $booking = $statuses->markCompleted($booking);
        $booking = $statuses->markClosed($booking);

        $this->assertSame(BookingStatus::Closed, $booking->status);
        $this->assertTrue($booking->statusLogs()->exists());
        $this->assertTrue($booking->lifecycleEvents()->where('event_key', 'closed')->exists());
    }

    public function test_no_show_host_unresponsive_and_dispute_freeze_states_work(): void
    {
        $statuses = app(BookingStatusService::class);

        $noShow = $this->manualBooking(['status' => BookingStatus::ReadyForCheckInCore->value]);
        $this->assertSame(BookingStatus::NoShow, $statuses->markNoShow($noShow)->status);

        $hostUnresponsive = $this->manualBooking(['status' => BookingStatus::ReadyForCheckInCore->value]);
        $this->assertSame(BookingStatus::HostUnresponsive, $statuses->markHostUnresponsive($hostUnresponsive)->status);

        $disputed = $this->manualBooking(['status' => BookingStatus::Confirmed->value]);
        $disputed = $statuses->markDisputed($disputed);
        $frozen = $statuses->freezeUntilDisputeResolved($disputed);

        $this->assertTrue($frozen->has_dispute);
        $this->assertSame(BookingStatus::FrozenUntilDisputeResolved, $frozen->status);
    }

    public function test_group_booking_creates_individual_child_bookings_and_two_guest_place_is_supported(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $property = Property::factory()->create(['host_user_id' => $host->id]);
        $room = Room::factory()->for($property)->create(['user_id' => $host->id]);
        $firstPlace = $this->sleepingPlaceFor($host, $property, $room, instant: true);
        $secondPlace = $this->sleepingPlaceFor($host, $property, $room, instant: true);

        $bookings = app(BookingGroupService::class)->createGroupBooking($guest, [
            $this->quoteFor($guest, $firstPlace, '2026-12-10', '2026-12-13'),
            $this->quoteFor($guest, $secondPlace, '2026-12-10', '2026-12-13'),
        ]);

        $this->assertCount(2, $bookings);
        $this->assertCount(2, $bookings->pluck('sleeping_place_id')->unique());
        $this->assertDatabaseCount('booking_group_links', 2);

        $doublePlace = $this->sleepingPlaceFor($host, $property, $room, instant: true, maxGuests: 2);
        $doubleBooking = app(BookingCreationService::class)->createInstantBooking($guest, $this->quoteFor($guest, $doublePlace, '2027-01-10', '2027-01-13', guests: 2));

        $this->assertSame('two_guests_one_double_place', $doubleBooking->guest_group_type);
        $this->assertSame(2, $doubleBooking->bookingGuests()->count());
    }

    public function test_privacy_filters_and_livewire_pages_render_in_english_and_russian(): void
    {
        [$guest, $host, $place] = $this->placeSetup(instant: true);
        $booking = app(BookingCreationService::class)->createInstantBooking($guest, $this->quoteFor($guest, $place, '2027-02-10', '2027-02-13'));
        $otherGuest = User::factory()->create();
        $otherHost = User::factory()->host()->create();
        $privacy = app(BookingPrivacyService::class);

        $this->assertNotEmpty($privacy->filterForGuest($guest, $booking));
        $this->assertSame([], $privacy->filterForGuest($otherGuest, $booking));
        $this->assertNotEmpty($privacy->filterForHost($host, $booking));
        $this->assertSame([], $privacy->filterForHost($otherHost, $booking));

        app()->setLocale('en');
        Livewire::actingAs($guest)
            ->test(GuestBookingPage::class, ['bookingId' => $booking->id])
            ->assertSee(__('bookings.cards.price', [], 'en'));

        app()->setLocale('ru');
        Livewire::actingAs($host)
            ->test(HostBookingsPage::class, ['hostUserId' => $host->id])
            ->assertSee(__('bookings.host.title', [], 'ru'));
    }

    /**
     * @return array{0:User,1:User,2:SleepingPlace}
     */
    private function placeSetup(bool $instant = false): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $property = Property::factory()->create(['host_user_id' => $host->id]);
        $room = Room::factory()->for($property)->create(['user_id' => $host->id]);
        $place = $this->sleepingPlaceFor($host, $property, $room, $instant);

        return [$guest, $host, $place];
    }

    private function sleepingPlaceFor(User $host, Property $property, Room $room, bool $instant = true, int $maxGuests = 1): SleepingPlace
    {
        return SleepingPlace::factory()->for($property)->for($room)->create([
            'user_id' => $host->id,
            'base_price_per_night' => 20,
            'base_price' => 20,
            'weekend_price' => null,
            'cleaning_fee' => 10,
            'deposit_amount' => 50,
            'max_guests' => $maxGuests,
            'max_guests_count' => $maxGuests,
            'min_guest_age' => null,
            'instant_booking_enabled' => $instant,
            'requires_host_approval' => ! $instant,
        ]);
    }

    private function quoteFor(User $guest, SleepingPlace $place, string $checkIn, string $checkOut, int $guests = 1): BookingQuote
    {
        return app(BookingPriceQuoteService::class)->createQuote($guest, $place, [
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests_count' => $guests,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function manualBooking(array $overrides = []): Booking
    {
        [$guest, $host, $place] = $this->placeSetup(instant: true);

        return Booking::factory()->create([
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => '2027-03-10',
            'check_out_date' => '2027-03-13',
            'nights_count' => 3,
            'chargeable_days_count' => 3,
            'calendar_presence_days_count' => 4,
            'total_payable' => 100,
            'total_amount' => 100,
            'currency' => 'EUR',
            ...$overrides,
        ]);
    }
}
