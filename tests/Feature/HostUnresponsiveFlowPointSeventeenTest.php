<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\HostUnresponsive\GuestHostUnresponsivePage;
use App\Livewire\Host\HostUnresponsive\HostUnresponsiveDetailsSheet;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\HostRepresentative;
use App\Models\HostUnresponsiveMedia;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\User;
use App\Services\Bookings\BookingNoShowDetectionService;
use App\Services\Bookings\BookingNoShowService;
use App\Services\Bookings\CancellationPolicySnapshotService;
use App\Services\Bookings\HostUnresponsiveCancellationIntegrationService;
use App\Services\Bookings\HostUnresponsiveDecisionService;
use App\Services\Bookings\HostUnresponsiveGuestActionService;
use App\Services\Bookings\HostUnresponsiveHostResponseService;
use App\Services\Bookings\HostUnresponsivePolicyService;
use App\Services\Bookings\HostUnresponsivePolicySnapshotService;
use App\Services\Bookings\HostUnresponsivePrivacyService;
use App\Services\Bookings\HostUnresponsiveRepresentativeResponseService;
use App\Services\Bookings\HostUnresponsiveService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class HostUnresponsiveFlowPointSeventeenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-10 18:20:00');
        CarbonImmutable::setTestNow('2026-07-10 18:20:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_point_seventeen_schema_exists_with_core_indexes(): void
    {
        foreach ([
            'host_unresponsive_policies',
            'host_unresponsive_policy_snapshots',
            'booking_host_unresponsive_cases',
            'host_unresponsive_contact_attempts',
            'host_unresponsive_guest_actions',
            'host_unresponsive_host_responses',
            'host_unresponsive_representative_responses',
            'host_unresponsive_media',
            'host_unresponsive_status_logs',
            'host_unresponsive_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table.' table is missing.');
        }

        $this->assertTrue(Schema::hasColumn('booking_host_unresponsive_cases', 'future_support_review_required'));
        $this->assertTrue(Schema::hasColumn('host_unresponsive_policy_snapshots', 'policy_snapshot_json'));
        $this->assertTrue(Schema::hasColumn('host_unresponsive_contact_attempts', 'target_type'));
    }

    public function test_policy_and_snapshot_are_created_for_booking(): void
    {
        [, $host, $place, $booking] = $this->createReadyForCheckInBooking();

        app(HostUnresponsivePolicyService::class)->updateForSleepingPlace($host, $place, [
            'check_in_response_minutes' => 45,
            'guest_waiting_outside_response_minutes' => 12,
            'auto_block_no_show_while_active' => true,
        ]);

        $snapshot = app(HostUnresponsivePolicySnapshotService::class)->createForBooking($booking);

        app(HostUnresponsivePolicyService::class)->updateForSleepingPlace($host, $place, [
            'check_in_response_minutes' => 90,
        ]);

        $this->assertSame(45, $snapshot->check_in_response_minutes);
        $this->assertSame(12, $snapshot->guest_waiting_outside_response_minutes);
        $this->assertTrue((bool) $snapshot->auto_block_no_show_while_active);
    }

    public function test_guest_can_report_host_unresponsive_and_contacts_are_created(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $representativeUser = User::factory()->create();
        $representative = HostRepresentative::factory()->create([
            'host_user_id' => $host->id,
            'representative_user_id' => $representativeUser->id,
        ]);
        $otherGuest = User::factory()->create();

        $case = app(HostUnresponsiveService::class)->createFromGuestReport($guest, $booking, [
            'case_type' => 'check_in_no_response',
            'reason_key' => 'guest_waiting_outside',
            'message' => 'I am at the door.',
            'guest_waiting_outside' => true,
        ]);

        $this->assertSame('representative_contact_attempted', $case->fresh()->status);
        $this->assertSame($representative->id, $case->host_representative_id);
        $this->assertSame($booking->sleeping_place_id, $case->sleeping_place_id);
        $this->assertSame($booking->room_id, $case->room_id);
        $this->assertSame($booking->property_id, $case->property_id);
        $this->assertDatabaseHas('notifications', ['user_id' => $host->id, 'type' => 'host_unresponsive_urgent']);
        $this->assertDatabaseHas('notifications', ['user_id' => $representativeUser->id, 'type' => 'host_unresponsive_representative_urgent']);
        $this->assertDatabaseHas('host_unresponsive_contact_attempts', ['host_unresponsive_case_id' => $case->id, 'target_type' => 'host']);
        $this->assertDatabaseHas('host_unresponsive_contact_attempts', ['host_unresponsive_case_id' => $case->id, 'target_type' => 'host_representative']);
        $this->assertDatabaseHas('host_unresponsive_guest_actions', ['host_unresponsive_case_id' => $case->id, 'action_type' => 'reported_host_not_answering']);
        $this->assertSame('host_unresponsive', $case->checkIn?->fresh()->status);

        $this->expectException(ValidationException::class);
        app(HostUnresponsiveService::class)->createFromGuestReport($otherGuest, $booking, []);
    }

    public function test_guest_actions_and_instruction_disclosures_are_recorded(): void
    {
        [$guest, , , $booking, $checkIn] = $this->createReadyForCheckInBooking();
        $checkIn->forceFill([
            'door_code_provided' => true,
            'intercom_code_provided' => true,
            'key_safe_code_provided' => true,
        ])->save();

        $case = app(HostUnresponsiveService::class)->createFromGuestReport($guest, $booking, [
            'reason_key' => 'door_code_not_working',
            'message' => 'The code fails.',
        ]);

        app(HostUnresponsiveGuestActionService::class)->markAtAddress($guest, $case, 'Near the entrance.');
        app(HostUnresponsiveGuestActionService::class)->markWaitingOutside($guest, $case->fresh(), 'Outside.');
        app(HostUnresponsiveGuestActionService::class)->markFeelsUnsafe($guest, $case->fresh(), 'Dark street.');

        $case = $case->fresh();

        $this->assertTrue((bool) $case->guest_at_address);
        $this->assertTrue((bool) $case->guest_waiting_outside);
        $this->assertTrue((bool) $case->guest_feels_unsafe);
        $this->assertTrue((bool) $case->exact_address_was_shown);
        $this->assertTrue((bool) $case->door_code_was_shown);
        $this->assertDatabaseHas('booking_check_in_access_disclosures', [
            'booking_check_in_id' => $checkIn->id,
            'guest_user_id' => $guest->id,
            'disclosure_type' => 'door_code',
        ]);
    }

    public function test_active_host_unresponsive_blocks_and_confirmed_case_rejects_no_show(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $noShow = app(BookingNoShowService::class)->createFromHostReport($host, $booking, []);
        $case = app(HostUnresponsiveService::class)->createFromGuestReport($guest, $booking, [
            'guest_marked_arrived' => true,
            'guest_waiting_outside' => true,
        ]);

        $this->assertFalse(app(BookingNoShowDetectionService::class)->canConfirmNoShow($noShow->fresh()));

        Carbon::setTestNow('2026-07-10 19:30:00');
        CarbonImmutable::setTestNow('2026-07-10 19:30:00');

        $confirmed = app(HostUnresponsiveDecisionService::class)->confirmHostUnresponsive($case->fresh(), $guest);

        $this->assertSame('unresolved', $confirmed->status);
        $this->assertSame('confirmed_host_unresponsive', $confirmed->decision_key);
        $this->assertSame(BookingStatus::HostUnresponsive, $booking->fresh()->status);
        $this->assertSame('failed', $confirmed->checkIn?->status);
        $this->assertSame('rejected_no_show', $noShow->fresh()->status);
        $this->assertSame($confirmed->id, $noShow->fresh()->host_unresponsive_case_id);
    }

    public function test_host_and_representative_responses_can_resolve_case(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $representative = HostRepresentative::factory()->create(['host_user_id' => $host->id]);
        $case = app(HostUnresponsiveService::class)->createFromGuestReport($guest, $booking, [
            'guest_waiting_outside' => true,
        ]);

        app(HostUnresponsiveHostResponseService::class)->markAvailable($host, $case, 'I am here.');
        app(HostUnresponsiveRepresentativeResponseService::class)->markAccessHelped($case->fresh());
        $resolved = app(HostUnresponsiveDecisionService::class)->markAccessResolved($case->fresh());

        $this->assertSame($representative->id, $case->fresh()->host_representative_id);
        $this->assertSame('access_resolved', $resolved->status);
        $this->assertNotNull($resolved->host_last_response_at);
        $this->assertNotNull($resolved->representative_last_response_at);
        $this->assertSame('check_in_continued', $resolved->checkIn?->fresh()->status);

        $otherHost = User::factory()->host()->create();
        $this->expectException(AuthorizationException::class);
        app(HostUnresponsiveHostResponseService::class)->markAvailable($otherHost, $resolved, 'Nope.');
    }

    public function test_unresolved_case_can_create_guest_friendly_cancellation_refund_and_release_locks(): void
    {
        [$guest, , $place, $booking] = $this->createReadyForCheckInBooking([
            'total' => 175,
            'total_amount' => 175,
            'total_payable' => 175,
        ]);
        app(CancellationPolicySnapshotService::class)->createForBooking($booking);

        foreach (['2026-07-10', '2026-07-11'] as $date) {
            SleepingPlaceBookingDateLock::factory()->create([
                'sleeping_place_id' => $place->id,
                'booking_id' => $booking->id,
                'date' => $date,
                'lock_type' => 'booked',
                'status' => 'active',
            ]);
        }

        $case = app(HostUnresponsiveService::class)->createFromGuestReport($guest, $booking, [
            'guest_waiting_outside' => true,
        ]);
        Carbon::setTestNow('2026-07-10 19:30:00');
        CarbonImmutable::setTestNow('2026-07-10 19:30:00');
        app(HostUnresponsiveDecisionService::class)->confirmHostUnresponsive($case->fresh(), $guest);

        $preview = app(HostUnresponsiveCancellationIntegrationService::class)->createCancellationPreview($case->fresh());
        $cancellation = app(HostUnresponsiveDecisionService::class)->convertToCancellation($case->fresh());

        $this->assertSame('host_unresponsive_related', $preview->cancellation_type);
        $this->assertSame('host_unresponsive', $preview->reason_key);
        $this->assertNotNull($cancellation->booking_refund_id);
        $this->assertSame(175.0, (float) $case->fresh()->refund_amount);
        $this->assertSame(0, SleepingPlaceBookingDateLock::query()->where('booking_id', $booking->id)->where('status', 'active')->count());
        $this->assertDatabaseHas('host_unresponsive_events', [
            'host_unresponsive_case_id' => $case->id,
            'event_key' => 'cancellation_created',
        ]);
    }

    public function test_privacy_media_and_livewire_render_in_english_and_russian(): void
    {
        [$guest, $host, , $booking] = $this->createReadyForCheckInBooking();
        $case = app(HostUnresponsiveService::class)->createFromGuestReport($guest, $booking, []);
        $otherGuest = User::factory()->create();
        $otherHost = User::factory()->host()->create();
        $media = HostUnresponsiveMedia::factory()->create([
            'host_unresponsive_case_id' => $case->id,
            'booking_id' => $booking->id,
            'uploaded_by_user_id' => $guest->id,
            'visibility' => 'guest_only',
        ]);
        $privacy = app(HostUnresponsivePrivacyService::class);

        $this->assertTrue($privacy->canGuestView($guest, $case));
        $this->assertFalse($privacy->canGuestView($otherGuest, $case));
        $this->assertTrue($privacy->canHostView($host, $case));
        $this->assertFalse($privacy->canHostView($otherHost, $case));
        $this->assertArrayNotHasKey('future_support_comment', $privacy->filterForGuest($guest, $case));
        $this->assertArrayNotHasKey('future_support_comment', $privacy->filterForHost($host, $case));
        $this->assertTrue($privacy->canViewMedia($guest, $media));
        $this->assertFalse($privacy->canViewMedia($host, $media));

        app()->setLocale('en');

        Livewire::actingAs($guest)
            ->test(GuestHostUnresponsivePage::class, ['case' => $case])
            ->assertSee(__('host_unresponsive.title'))
            ->assertSee(__('host_unresponsive.actions.request_cancellation'));

        Livewire::actingAs($host)
            ->test(HostUnresponsiveDetailsSheet::class, ['case' => $case])
            ->assertSee(__('host_unresponsive.host_title'))
            ->assertSee(__('host_unresponsive.actions.send_instruction'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(GuestHostUnresponsivePage::class, ['case' => $case])
            ->assertSee(__('host_unresponsive.title'))
            ->assertSee(__('host_unresponsive.actions.request_cancellation'));

        Livewire::actingAs($host)
            ->test(HostUnresponsiveDetailsSheet::class, ['case' => $case])
            ->assertSee(__('host_unresponsive.host_title'))
            ->assertSee(__('host_unresponsive.actions.send_instruction'));
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
                'display_name' => 'Host help lower bed',
                'place_number' => 'HU1',
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
            'check_in_instruction_available' => true,
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
            'instructions_available_at' => now(),
            'status' => 'instructions_available',
        ]);

        return [$guest, $host, $place, $booking, $checkIn];
    }
}
