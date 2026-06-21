<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\Mismatch\GuestMismatchPage;
use App\Livewire\Host\Mismatch\HostMismatchDetailsSheet;
use App\Models\Booking;
use App\Models\BookingCancellation;
use App\Models\BookingCheckIn;
use App\Models\BookingListingMismatchMedia;
use App\Models\BookingStay;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Bookings\ListingMismatchCancellationIntegrationService;
use App\Services\Bookings\ListingMismatchCleaningIntegrationService;
use App\Services\Bookings\ListingMismatchComplaintIntegrationService;
use App\Services\Bookings\ListingMismatchGuestResponseService;
use App\Services\Bookings\ListingMismatchHostResponseService;
use App\Services\Bookings\ListingMismatchItemService;
use App\Services\Bookings\ListingMismatchMaintenanceIntegrationService;
use App\Services\Bookings\ListingMismatchMediaService;
use App\Services\Bookings\ListingMismatchPrivacyService;
use App\Services\Bookings\ListingMismatchRatingIntegrationService;
use App\Services\Bookings\ListingMismatchRefundIntegrationService;
use App\Services\Bookings\ListingMismatchRelocationIntegrationService;
use App\Services\Bookings\ListingMismatchSearchIntegrationService;
use App\Services\Bookings\ListingMismatchService;
use App\Services\Bookings\ListingMismatchSnapshotCompareService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ListingMismatchFlowPointEighteenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-11 09:30:00');
        CarbonImmutable::setTestNow('2026-07-11 09:30:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_point_eighteen_schema_exists_with_core_indexes(): void
    {
        foreach ([
            'booking_listing_mismatch_reports',
            'booking_listing_mismatch_items',
            'booking_listing_mismatch_media',
            'booking_listing_mismatch_host_responses',
            'booking_listing_mismatch_guest_responses',
            'booking_listing_mismatch_resolution_options',
            'booking_listing_mismatch_compensation_lines',
            'booking_listing_mismatch_warnings',
            'booking_listing_mismatch_status_logs',
            'booking_listing_mismatch_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table.' table is missing.');
        }

        $this->assertTrue(Schema::hasColumn('booking_listing_mismatch_reports', 'future_review_required'));
        $this->assertTrue(Schema::hasColumn('booking_listing_mismatch_reports', 'snapshot_compared'));
        $this->assertTrue(Schema::hasColumn('booking_listing_mismatch_warnings', 'blocking'));
    }

    public function test_guest_can_report_mismatch_and_snapshot_compare_generates_warnings(): void
    {
        [$guest, , , $booking] = $this->createActiveBooking([
            '_snapshots' => [
                'listing' => [
                    'has_wifi' => true,
                    'has_locker' => true,
                    'quiet_room' => false,
                    'bed_type' => 'single',
                    'bunk_level' => 'bottom',
                ],
            ],
        ]);

        $report = app(ListingMismatchService::class)->createFromGuestReport($guest, $booking, [
            'mismatch_type' => 'missing_wifi',
            'severity' => 'high',
            'guest_description' => 'Wi-Fi was promised but is not available.',
            'what_was_actual' => 'No network is visible.',
            'guest_wants_fix' => true,
            'guest_wants_compensation' => true,
            'items' => [
                [
                    'item_key' => 'has_wifi',
                    'item_type' => 'property_amenity',
                    'actual_value' => 'missing',
                    'severity' => 'high',
                ],
            ],
        ]);

        $comparison = app(ListingMismatchSnapshotCompareService::class)->compareAmenity($report->fresh(), 'has_wifi');
        app(ListingMismatchSnapshotCompareService::class)->compareAmenity($report->fresh(), 'quiet_room');

        $report = $report->fresh(['items', 'warnings']);

        $this->assertSame($booking->id, $report->booking_id);
        $this->assertSame($booking->sleeping_place_id, $report->sleeping_place_id);
        $this->assertSame($booking->room_id, $report->room_id);
        $this->assertSame($booking->property_id, $report->property_id);
        $this->assertTrue((bool) $report->snapshot_compared);
        $this->assertGreaterThanOrEqual(0.8, (float) $comparison['confidence']);
        $this->assertDatabaseHas('booking_listing_mismatch_warnings', [
            'booking_listing_mismatch_report_id' => $report->id,
            'warning_key' => 'claimed_missing_amenity_was_listed',
        ]);
        $this->assertDatabaseHas('booking_listing_mismatch_warnings', [
            'booking_listing_mismatch_report_id' => $report->id,
            'warning_key' => 'claimed_feature_was_not_listed',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $booking->host_user_id,
            'type' => 'listing_mismatch_reported',
        ]);
    }

    public function test_guest_cannot_report_for_another_booking_and_privacy_hides_future_fields(): void
    {
        [$guest, $host, , $booking] = $this->createActiveBooking();
        $otherGuest = User::factory()->create();
        $otherHost = User::factory()->host()->create();

        $report = app(ListingMismatchService::class)->createFromGuestReport($guest, $booking, [
            'mismatch_type' => 'missing_locker',
            'future_review_comment' => 'Internal future review note.',
        ]);

        $privacy = app(ListingMismatchPrivacyService::class);

        $this->assertTrue($privacy->canGuestView($guest, $report));
        $this->assertFalse($privacy->canGuestView($otherGuest, $report));
        $this->assertTrue($privacy->canHostView($host, $report));
        $this->assertFalse($privacy->canHostView($otherHost, $report));
        $this->assertArrayNotHasKey('future_review_comment', $privacy->filterForGuest($guest, $report));
        $this->assertArrayNotHasKey('future_review_comment', $privacy->filterForHost($host, $report));

        $media = BookingListingMismatchMedia::factory()->create([
            'booking_listing_mismatch_report_id' => $report->id,
            'booking_id' => $booking->id,
            'uploaded_by_user_id' => $guest->id,
            'visibility' => 'guest_only',
        ]);

        $this->assertTrue($privacy->canViewMedia($guest, $media));
        $this->assertFalse($privacy->canViewMedia($host, $media));

        $this->expectException(ValidationException::class);
        app(ListingMismatchService::class)->createFromGuestReport($otherGuest, $booking, [
            'mismatch_type' => 'missing_wifi',
        ]);
    }

    public function test_items_media_host_and_guest_responses_drive_resolution_options(): void
    {
        [$guest, $host, $place, $booking] = $this->createActiveBooking();
        $alternativePlace = SleepingPlace::factory()
            ->for($place->room)
            ->for($place->property)
            ->create([
                'base_price_per_night' => 55,
                'base_price' => 55,
            ]);
        $report = app(ListingMismatchService::class)->createFromGuestReport($guest, $booking, [
            'mismatch_type' => 'missing_locker',
            'severity' => 'medium',
        ]);

        $items = app(ListingMismatchItemService::class)->createItemsFromReport($report, [
            ['item_key' => 'has_locker', 'item_type' => 'sleeping_place_feature', 'severity' => 'medium'],
        ]);
        app(ListingMismatchItemService::class)->confirmItem($items->first());
        app(ListingMismatchItemService::class)->rejectItem($items->first()->fresh());
        app(ListingMismatchMediaService::class)->uploadGuestEvidence($guest, $report, [
            'path' => 'mismatch/guest-locker.jpg',
            'media_role' => 'missing_amenity_evidence',
        ]);
        app(ListingMismatchMediaService::class)->uploadHostEvidence($host, $report, [
            'path' => 'mismatch/host-locker.jpg',
            'media_role' => 'host_fix_evidence',
        ]);

        app(ListingMismatchHostResponseService::class)->accept($host, $report, 'I will fix this.');
        app(ListingMismatchHostResponseService::class)->deny($host, $report, 'The locker is in the hall.');
        app(ListingMismatchHostResponseService::class)->askForMoreEvidence($host, $report, 'Please add a photo.');
        app(ListingMismatchHostResponseService::class)->offerFix($host, $report, 'I can bring a locker.');
        app(ListingMismatchHostResponseService::class)->offerCleaning($host, $report, 'Cleaner can come today.');
        app(ListingMismatchHostResponseService::class)->offerRepair($host, $report, 'Repair requested.');
        app(ListingMismatchHostResponseService::class)->offerRelocation($host, $report, $alternativePlace);
        app(ListingMismatchHostResponseService::class)->offerRefund($host, $report, 20);
        app(ListingMismatchHostResponseService::class)->offerCompensation($host, $report, 12);

        app(ListingMismatchGuestResponseService::class)->requestRelocation($guest, $report);
        app(ListingMismatchGuestResponseService::class)->requestCancellation($guest, $report);
        app(ListingMismatchGuestResponseService::class)->requestRefund($guest, $report, 20);
        app(ListingMismatchGuestResponseService::class)->rejectResolution($guest, $report, 'Need a better option.');
        $accepted = app(ListingMismatchGuestResponseService::class)->acceptResolution($guest, $report, [
            'accepted_resolution_type' => 'partial_refund',
            'accepted_refund_amount' => 20,
        ]);

        $this->assertSame('accept_resolution', $accepted->response_type);
        $this->assertDatabaseHas('booking_listing_mismatch_media', ['booking_listing_mismatch_report_id' => $report->id, 'media_role' => 'missing_amenity_evidence']);
        $this->assertDatabaseHas('booking_listing_mismatch_media', ['booking_listing_mismatch_report_id' => $report->id, 'media_role' => 'host_fix_evidence']);
        $this->assertDatabaseHas('booking_listing_mismatch_resolution_options', ['booking_listing_mismatch_report_id' => $report->id, 'resolution_type' => 'relocation']);
        $this->assertDatabaseHas('booking_listing_mismatch_resolution_options', ['booking_listing_mismatch_report_id' => $report->id, 'resolution_type' => 'partial_refund']);
        $this->assertDatabaseHas('booking_listing_mismatch_resolution_options', ['booking_listing_mismatch_report_id' => $report->id, 'resolution_type' => 'compensation']);

        $this->expectException(AuthorizationException::class);
        app(ListingMismatchHostResponseService::class)->accept(User::factory()->host()->create(), $report, 'No access.');
    }

    public function test_confirmed_mismatch_can_create_resolution_integrations_and_search_rating_events(): void
    {
        [$guest, $host, $place, $booking] = $this->createActiveBooking([
            '_snapshots' => ['listing' => ['has_locker' => true]],
        ]);
        $alternativePlace = SleepingPlace::factory()
            ->for($place->room)
            ->for($place->property)
            ->create([
                'base_price_per_night' => 50,
                'base_price' => 50,
            ]);
        $report = app(ListingMismatchService::class)->createFromGuestReport($guest, $booking, [
            'mismatch_type' => 'dirty_room',
            'severity' => 'unsafe',
            'guest_description' => 'The room is unsafe and dirty.',
            'guest_wants_relocation' => true,
            'guest_wants_refund' => true,
        ]);

        $report->forceFill([
            'status' => 'confirmed',
            'host_accepts_problem' => true,
            'resolution_type' => 'partial_refund',
            'refund_amount' => 30,
        ])->save();

        $refund = app(ListingMismatchRefundIntegrationService::class)->createPartialRefund($report->fresh(), 30);
        $relocation = app(ListingMismatchRelocationIntegrationService::class)->createRelocationFromMismatch($report->fresh(), $alternativePlace);
        $cleaning = app(ListingMismatchCleaningIntegrationService::class)->createCleaningIfNeeded($report->fresh());
        $maintenance = app(ListingMismatchMaintenanceIntegrationService::class)->createMaintenanceIfNeeded($report->fresh());
        $complaint = app(ListingMismatchComplaintIntegrationService::class)->createComplaintIfSeriousOrUnresolved($report->fresh());
        $cancellationPreview = app(ListingMismatchCancellationIntegrationService::class)->createCancellationPreview($report->fresh());
        app(ListingMismatchSearchIntegrationService::class)->markSleepingPlaceRequestOnlyIfSerious($report->fresh());
        app(ListingMismatchSearchIntegrationService::class)->hideSleepingPlaceIfUnsafe($report->fresh());
        app(ListingMismatchSearchIntegrationService::class)->createHostListingSuggestions($report->fresh());
        app(ListingMismatchRatingIntegrationService::class)->recordConfirmedMismatch($report->fresh());
        app(ListingMismatchRatingIntegrationService::class)->removeRatingImpactIfRejected($report->fresh());

        $report = $report->fresh();

        $this->assertSame($refund->id, $report->booking_refund_id);
        $this->assertSame($relocation->id, $report->booking_relocation_id);
        $this->assertSame($cleaning->id, $report->cleaning_task_id);
        $this->assertNotNull($maintenance);
        $this->assertSame($complaint->id, $report->complaint_case_id);
        $this->assertSame('listing_mismatch_related', $cancellationPreview->cancellation_type);
        $this->assertDatabaseHas('booking_listing_mismatch_warnings', [
            'booking_listing_mismatch_report_id' => $report->id,
            'warning_key' => 'unsafe_claim_requires_urgent_action',
            'blocking' => true,
        ]);
        $this->assertDatabaseHas('host_listing_suggestions', [
            'user_id' => $host->id,
            'sleeping_place_id' => $place->id,
            'suggestion_key' => 'listing_mismatch_update_listing',
        ]);
        $this->assertDatabaseHas('booking_listing_mismatch_events', [
            'booking_listing_mismatch_report_id' => $report->id,
            'event_key' => 'mismatch_confirmed',
        ]);
        $this->assertSame('hidden', $place->fresh()->status->value);
    }

    public function test_report_can_be_created_from_check_in_problem_cancellation_and_livewire_renders_in_two_locales(): void
    {
        [$guest, $host, , $booking, $checkIn] = $this->createActiveBooking();
        $checkInProblem = $checkIn->problems()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'problem_type' => 'listing_mismatch',
            'severity' => 'high',
            'status' => 'reported',
            'description' => 'This is not the listed bed.',
            'reported_at' => now(),
        ]);
        $cancellation = BookingCancellation::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'cancellation_type' => 'listing_mismatch_related',
            'reason_key' => 'listing_mismatch',
        ]);

        $fromProblem = app(ListingMismatchService::class)->createFromCheckInProblem($checkInProblem);
        $fromCancellation = app(ListingMismatchService::class)->createFromCancellation($cancellation);

        $this->assertSame('check_in_problem', $fromProblem->source_type);
        $this->assertSame('cancellation', $fromCancellation->source_type);

        app()->setLocale('en');

        Livewire::actingAs($guest)
            ->test(GuestMismatchPage::class, ['report' => $fromProblem])
            ->assertSee(__('listing_mismatch.title'))
            ->assertSee(__('listing_mismatch.actions.request_refund'));

        Livewire::actingAs($host)
            ->test(HostMismatchDetailsSheet::class, ['report' => $fromProblem])
            ->assertSee(__('listing_mismatch.host_title'))
            ->assertSee(__('listing_mismatch.actions.offer_relocation'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(GuestMismatchPage::class, ['report' => $fromProblem])
            ->assertSee(__('listing_mismatch.title'))
            ->assertSee(__('listing_mismatch.actions.request_refund'));

        Livewire::actingAs($host)
            ->test(HostMismatchDetailsSheet::class, ['report' => $fromProblem])
            ->assertSee(__('listing_mismatch.host_title'))
            ->assertSee(__('listing_mismatch.actions.offer_relocation'));
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array{0:User, 1:User, 2:SleepingPlace, 3:Booking, 4:BookingCheckIn, 5:BookingStay}
     */
    private function createActiveBooking(array $snapshot = []): array
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
                'display_name' => 'Mismatch lower bed',
                'place_number' => 'MM1',
                'base_price_per_night' => 50,
                'base_price' => 50,
                'has_locker' => true,
                'has_bedding' => true,
                'bunk_level' => 'bottom',
                'is_bottom_bunk' => true,
            ]);

        $booking = Booking::factory()->create([
            'bed_id' => null,
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => BookingStatus::InProgress,
            'payment_status' => PaymentStatus::Paid,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-14',
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-14',
            'check_in_time' => '18:00',
            'arrival_time' => '18:00',
            'nights_count' => 4,
            'nights' => 4,
            'chargeable_days_count' => 4,
            'calendar_presence_days_count' => 5,
            'subtotal' => 200,
            'subtotal_amount' => 200,
            'accommodation_amount' => 200,
            'discount_amount' => 0,
            'cleaning_fee' => 10,
            'cleaning_fee_amount' => 10,
            'deposit' => 50,
            'deposit_amount' => 50,
            'service_fee' => 20,
            'service_fee_amount' => 20,
            'tax_amount' => 0,
            'city_fee_amount' => 0,
            'total' => 280,
            'total_amount' => 280,
            'total_payable' => 280,
            'host_payout_amount' => 210,
            'currency' => 'EUR',
            'cancellation_policy' => CancellationPolicy::Flexible,
            'free_cancel_before' => '2026-07-09 18:00:00',
            'nightly_price_snapshot' => $snapshot ?: [
                '_snapshots' => [
                    'listing' => [
                        'has_wifi' => true,
                        'has_locker' => true,
                        'has_bedding' => true,
                        'bed_type' => 'single',
                        'bunk_level' => 'bottom',
                        'room_people_count' => 4,
                    ],
                ],
            ],
        ]);

        $checkIn = BookingCheckIn::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => '2026-07-10',
            'status' => 'completed',
        ]);

        $stay = BookingStay::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => 'active',
            'check_in_date' => '2026-07-10',
            'planned_check_out_date' => '2026-07-14',
            'nights_count' => 4,
            'calendar_presence_days_count' => 5,
        ]);

        return [$guest, $host, $place->fresh(['room', 'property']), $booking, $checkIn, $stay];
    }
}
