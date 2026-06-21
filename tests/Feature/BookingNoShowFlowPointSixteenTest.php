<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\NoShow\GuestNoShowPage;
use App\Livewire\Host\NoShow\HostNoShowDetailsSheet;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingNoShowGuestResponse;
use App\Models\BookingNoShowMedia;
use App\Models\BookingStay;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\User;
use App\Services\Bookings\BookingNoShowDecisionService;
use App\Services\Bookings\BookingNoShowDetectionService;
use App\Services\Bookings\BookingNoShowGuestResponseService;
use App\Services\Bookings\BookingNoShowPolicyService;
use App\Services\Bookings\BookingNoShowPolicySnapshotService;
use App\Services\Bookings\BookingNoShowPrivacyService;
use App\Services\Bookings\BookingNoShowService;
use App\Services\Bookings\CancellationPolicySnapshotService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BookingNoShowFlowPointSixteenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-10 19:00:00');
        CarbonImmutable::setTestNow('2026-07-10 19:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_point_sixteen_schema_exists_with_core_indexes(): void
    {
        foreach ([
            'booking_no_show_policies',
            'booking_no_show_policy_snapshots',
            'booking_no_shows',
            'booking_no_show_contact_attempts',
            'booking_no_show_guest_responses',
            'booking_no_show_media',
            'booking_no_show_status_logs',
            'booking_no_show_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table.' table is missing.');
        }

        $this->assertTrue(Schema::hasColumn('booking_no_shows', 'future_support_review_required'));
        $this->assertTrue(Schema::hasColumn('booking_no_show_policy_snapshots', 'policy_snapshot_json'));
        $this->assertTrue(Schema::hasColumn('booking_no_show_contact_attempts', 'contact_channel'));
    }

    public function test_no_show_policy_and_snapshot_are_created_for_sleeping_place_and_booking(): void
    {
        [, $host, $place, $booking] = $this->createReadyForCheckInBooking();

        $policy = app(BookingNoShowPolicyService::class)->updateForSleepingPlace($host, $place, [
            'waiting_period_minutes' => 120,
            'hold_first_night_on_no_show' => true,
            'refund_cleaning_fee_on_no_show' => false,
            'host_payout_rule' => 'first_night',
            'guest_penalty_rule' => 'first_night',
        ]);

        $snapshot = app(BookingNoShowPolicySnapshotService::class)->createForBooking($booking);

        $policy->forceFill([
            'waiting_period_minutes' => 30,
            'refund_cleaning_fee_on_no_show' => true,
        ])->save();

        $this->assertSame(120, $snapshot->waiting_period_minutes);
        $this->assertTrue((bool) $snapshot->hold_first_night_on_no_show);
        $this->assertFalse((bool) $snapshot->refund_cleaning_fee_on_no_show);
        $this->assertSame('first_night', $snapshot->guest_penalty_rule);
    }

    public function test_no_show_watch_can_start_only_for_allowed_booking_states(): void
    {
        [, , , $booking] = $this->createReadyForCheckInBooking();

        $watch = app(BookingNoShowService::class)->startWatchForBooking($booking);

        $this->assertSame('watching', $watch->status);
        $this->assertSame(180, $watch->waiting_period_minutes);
        $this->assertSame('2026-07-10 22:00:00', $watch->waiting_until?->format('Y-m-d H:i:s'));

        $cancelled = $this->createReadyForCheckInBooking(['status' => BookingStatus::CancelledByGuestFlow])[3];

        try {
            app(BookingNoShowService::class)->startWatchForBooking($cancelled);
            $this->fail('Cancelled booking accepted no-show watch.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $checkedIn = $this->createReadyForCheckInBooking(['status' => BookingStatus::GuestCheckedIn, 'guest_checked_in_at' => now()])[3];

        $this->expectException(ValidationException::class);
        app(BookingNoShowService::class)->startWatchForBooking($checkedIn);
    }

    public function test_host_can_report_no_show_for_own_booking_and_guest_is_contacted(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $otherHost = User::factory()->host()->create();

        $noShow = app(BookingNoShowService::class)->createFromHostReport($host, $booking, [
            'reason_key' => 'host_reported_guest_absent',
            'host_comment' => 'Guest has not arrived.',
        ]);

        $this->assertSame('host_reported', $noShow->status);
        $this->assertTrue((bool) $noShow->host_marked_no_show);
        $this->assertSame($host->id, $noShow->host_user_id);
        $this->assertSame($guest->id, $noShow->guest_user_id);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'booking_no_show_reported',
        ]);
        $this->assertDatabaseHas('booking_no_show_contact_attempts', [
            'booking_no_show_id' => $noShow->id,
            'attempt_type' => 'guest_check_request',
            'status' => 'sent',
        ]);

        $this->expectException(ValidationException::class);
        app(BookingNoShowService::class)->createFromHostReport($otherHost, $booking, []);
    }

    public function test_guest_late_response_extends_waiting_period_and_arrival_blocks_confirmation(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $noShow = app(BookingNoShowService::class)->createFromHostReport($host, $booking, []);

        app(BookingNoShowGuestResponseService::class)->markLate($guest, $noShow, '23:30', 'Traffic delay.');
        $lateNoShow = $noShow->fresh();

        $this->assertSame('guest_responded_late_arrival', $lateNoShow->status);
        $this->assertTrue((bool) $lateNoShow->guest_warned_late_arrival);
        $this->assertSame('23:30', BookingNoShowGuestResponse::query()->latest('id')->value('new_arrival_time'));
        $this->assertTrue($lateNoShow->waiting_until->greaterThan(CarbonImmutable::parse('2026-07-10 22:00:00')));

        app(BookingNoShowGuestResponseService::class)->markArrived($guest, $lateNoShow);

        $this->assertFalse(app(BookingNoShowDetectionService::class)->canConfirmNoShow($lateNoShow->fresh()));

        $this->expectException(ValidationException::class);
        app(BookingNoShowDecisionService::class)->confirmNoShow($host, $lateNoShow->fresh());
    }

    public function test_check_in_problem_and_host_not_answering_prevent_confirmed_no_show(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $noShow = app(BookingNoShowService::class)->createFromHostReport($host, $booking, []);

        app(BookingNoShowGuestResponseService::class)->reportCheckInProblem($guest, $noShow, [
            'message' => 'The access code does not work.',
        ]);

        $this->assertSame('converted_to_check_in_problem', $noShow->fresh()->decision_key);
        $this->assertFalse(app(BookingNoShowDetectionService::class)->canConfirmNoShow($noShow->fresh()));

        [$guestTwo, $hostTwo, , $bookingTwo] = $this->createReadyForCheckInBooking();
        $secondNoShow = app(BookingNoShowService::class)->createFromHostReport($hostTwo, $bookingTwo, []);

        app(BookingNoShowGuestResponseService::class)->reportHostNotAnswering($guestTwo, $secondNoShow, [
            'message' => 'I am at the door and nobody answers.',
        ]);

        $this->assertSame('converted_to_host_unresponsive', $secondNoShow->fresh()->status);
        $this->assertSame(BookingStatus::HostUnresponsive, $bookingTwo->fresh()->status);
    }

    public function test_waiting_period_controls_no_show_confirmation_and_guest_can_accept_no_show(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $noShow = app(BookingNoShowService::class)->createFromHostReport($host, $booking, []);

        $this->assertFalse(app(BookingNoShowDetectionService::class)->canConfirmNoShow($noShow));

        try {
            app(BookingNoShowDecisionService::class)->confirmNoShow($host, $noShow);
            $this->fail('No-show confirmed before waiting period expired.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        [$guestTwo, $hostTwo, , $bookingTwo] = $this->createReadyForCheckInBooking();
        $acceptedNoShow = app(BookingNoShowService::class)->createFromHostReport($hostTwo, $bookingTwo, []);
        BookingNoShowGuestResponse::query()->create([
            'booking_no_show_id' => $acceptedNoShow->id,
            'booking_id' => $bookingTwo->id,
            'guest_user_id' => $guestTwo->id,
            'response_type' => 'accept_no_show',
        ]);
        $acceptedNoShow->forceFill(['guest_response_type' => 'accept_no_show'])->save();

        $this->assertTrue(app(BookingNoShowDetectionService::class)->canConfirmNoShow($acceptedNoShow->fresh()));

        Carbon::setTestNow('2026-07-10 22:30:00');
        CarbonImmutable::setTestNow('2026-07-10 22:30:00');

        [, $hostThree, , $bookingThree] = $this->createReadyForCheckInBooking();
        $expiredNoShow = app(BookingNoShowService::class)->createFromHostReport($hostThree, $bookingThree, []);
        $expiredNoShow->forceFill(['waiting_until' => '2026-07-10 21:00:00'])->save();

        $this->assertTrue(app(BookingNoShowDetectionService::class)->canConfirmNoShow($expiredNoShow->fresh()));
    }

    public function test_confirmed_no_show_updates_booking_check_in_money_calendar_cancellation_and_refund(): void
    {
        [, $host, $place, $booking] = $this->createReadyForCheckInBooking([
            'check_out_date' => '2026-07-13',
            'check_out' => '2026-07-13',
            'nights_count' => 3,
            'nights' => 3,
            'subtotal' => 150,
            'subtotal_amount' => 150,
            'accommodation_amount' => 150,
            'cleaning_fee' => 12,
            'cleaning_fee_amount' => 12,
            'deposit' => 50,
            'deposit_amount' => 50,
            'service_fee' => 18,
            'service_fee_amount' => 18,
            'total' => 230,
            'total_amount' => 230,
            'total_payable' => 230,
        ]);
        app(CancellationPolicySnapshotService::class)->createForBooking($booking);
        app(BookingNoShowPolicyService::class)->updateForSleepingPlace($host, $place, [
            'waiting_period_minutes' => 120,
            'hold_first_night_on_no_show' => true,
            'release_remaining_nights_after_no_show' => true,
            'refund_deposit_on_no_show' => true,
            'refund_cleaning_fee_on_no_show' => true,
            'refund_service_fee_on_no_show' => false,
            'guest_penalty_rule' => 'first_night',
            'host_payout_rule' => 'first_night',
        ]);

        foreach (['2026-07-10', '2026-07-11', '2026-07-12'] as $date) {
            SleepingPlaceBookingDateLock::factory()->create([
                'sleeping_place_id' => $place->id,
                'booking_id' => $booking->id,
                'date' => $date,
                'lock_type' => 'booked',
                'status' => 'active',
            ]);
        }

        $noShow = app(BookingNoShowService::class)->createFromHostReport($host, $booking, []);
        $noShow->forceFill(['waiting_until' => '2026-07-10 18:30:00'])->save();

        $confirmed = app(BookingNoShowDecisionService::class)->confirmNoShow($host, $noShow->fresh());

        $this->assertSame('confirmed_no_show', $confirmed->status);
        $this->assertSame(BookingStatus::NoShow, $booking->fresh()->status);
        $this->assertSame('failed', $confirmed->checkIn?->status);
        $this->assertSame(0, BookingStay::query()->where('booking_id', $booking->id)->count());
        $this->assertSame(62.0, (float) $confirmed->refund_amount);
        $this->assertSame(50.0, (float) $confirmed->penalty_amount);
        $this->assertSame(50.0, (float) $confirmed->deposit_refund_amount);
        $this->assertSame(12.0, (float) $confirmed->cleaning_fee_refund_amount);
        $this->assertSame(0.0, (float) $confirmed->service_fee_refund_amount);
        $this->assertSame(50.0, (float) $confirmed->host_payout_amount);
        $this->assertSame('released_remaining_dates', $confirmed->calendar_release_status);
        $this->assertNotNull($confirmed->booking_cancellation_id);
        $this->assertNotNull($confirmed->booking_refund_id);
        $this->assertSame(1, SleepingPlaceBookingDateLock::query()->where('booking_id', $booking->id)->where('status', 'active')->count());
        $this->assertSame(2, SleepingPlaceBookingDateLock::query()->where('booking_id', $booking->id)->where('status', 'released')->count());
        $this->assertDatabaseHas('booking_no_show_events', [
            'booking_no_show_id' => $confirmed->id,
            'event_key' => 'waitlist_notified',
        ]);
        $this->assertDatabaseHas('booking_no_show_events', [
            'booking_no_show_id' => $confirmed->id,
            'event_key' => 'saved_searches_notified',
        ]);
    }

    public function test_guest_can_dispute_no_show_and_privacy_filters_hide_future_support_fields(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $otherGuest = User::factory()->create();
        $otherHost = User::factory()->host()->create();
        $noShow = app(BookingNoShowService::class)->createFromHostReport($host, $booking, []);

        app(BookingNoShowGuestResponseService::class)->disputeNoShow($guest, $noShow, 'I arrived and waited outside.');

        $disputed = $noShow->fresh();
        $privacy = app(BookingNoShowPrivacyService::class);

        $this->assertSame('dispute_opened', $disputed->status);
        $this->assertTrue((bool) $disputed->future_support_review_required);
        $this->assertSame(BookingStatus::DisputeOpened, $booking->fresh()->status);
        $this->assertTrue($privacy->canGuestView($guest, $disputed));
        $this->assertFalse($privacy->canGuestView($otherGuest, $disputed));
        $this->assertTrue($privacy->canHostView($host, $disputed));
        $this->assertFalse($privacy->canHostView($otherHost, $disputed));
        $this->assertArrayNotHasKey('future_support_comment', $privacy->filterForGuest($guest, $disputed));
        $this->assertArrayNotHasKey('future_support_comment', $privacy->filterForHost($host, $disputed));
    }

    public function test_no_show_media_visibility_is_respected(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $noShow = app(BookingNoShowService::class)->createFromHostReport($host, $booking, []);
        $media = BookingNoShowMedia::factory()->create([
            'booking_no_show_id' => $noShow->id,
            'booking_id' => $booking->id,
            'uploaded_by_user_id' => $guest->id,
            'visibility' => 'guest_only',
        ]);

        $privacy = app(BookingNoShowPrivacyService::class);

        $this->assertTrue($privacy->canViewMedia($guest, $media));
        $this->assertFalse($privacy->canViewMedia($host, $media));
    }

    public function test_guest_and_host_no_show_components_render_in_english_and_russian(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $noShow = app(BookingNoShowService::class)->createFromHostReport($host, $booking, []);

        app()->setLocale('en');

        Livewire::actingAs($guest)
            ->test(GuestNoShowPage::class, ['noShow' => $noShow])
            ->assertSee(__('no_show.title'))
            ->assertSee(__('no_show.guest_responses.i_am_late'));

        Livewire::actingAs($host)
            ->test(HostNoShowDetailsSheet::class, ['noShow' => $noShow])
            ->assertSee(__('no_show.host_title'))
            ->assertSee(__('no_show.actions.confirm_no_show'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(GuestNoShowPage::class, ['noShow' => $noShow])
            ->assertSee(__('no_show.title'))
            ->assertSee(__('no_show.guest_responses.i_am_late'));

        Livewire::actingAs($host)
            ->test(HostNoShowDetailsSheet::class, ['noShow' => $noShow])
            ->assertSee(__('no_show.host_title'))
            ->assertSee(__('no_show.actions.confirm_no_show'));
    }

    /**
     * @param  array<string, mixed>  $bookingOverrides
     * @return array{0:User, 1:User, 2:SleepingPlace, 3:Booking, 4:BookingCheckIn}
     */
    private function createReadyForCheckInBooking(array $bookingOverrides = []): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'city' => 'Vilnius',
            ]);
        $room = Room::factory()->for($property)->create();
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'display_name' => 'No-show lower bed',
                'place_number' => 'NS1',
                'base_price_per_night' => 50,
                'base_price' => 50,
            ]);

        $booking = Booking::factory()->create(array_merge([
            'bed_id' => null,
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => BookingStatus::ReadyForCheckInCore,
            'payment_status' => PaymentStatus::Paid,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-12',
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-12',
            'check_in_time' => '18:00',
            'arrival_time' => '18:00',
            'nights_count' => 2,
            'nights' => 2,
            'chargeable_days_count' => 2,
            'calendar_presence_days_count' => 3,
            'subtotal' => 100,
            'subtotal_amount' => 100,
            'accommodation_amount' => 100,
            'discount_amount' => 0,
            'cleaning_fee' => 10,
            'cleaning_fee_amount' => 10,
            'deposit' => 50,
            'deposit_amount' => 50,
            'service_fee' => 15,
            'service_fee_amount' => 15,
            'tax_amount' => 0,
            'city_fee_amount' => 0,
            'total' => 175,
            'total_amount' => 175,
            'total_payable' => 175,
            'host_payout_amount' => 110,
            'currency' => 'EUR',
            'cancellation_policy' => CancellationPolicy::Flexible,
            'free_cancel_before' => '2026-07-09 18:00:00',
        ], $bookingOverrides));

        $checkIn = BookingCheckIn::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => '2026-07-10',
            'planned_check_in_time' => '18:00',
            'planned_check_in_window' => '18:00-22:00',
            'check_in_window' => '18:00-22:00',
            'status' => 'instructions_available',
        ]);

        return [$guest, $host, $place, $booking, $checkIn];
    }
}
