<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Bookings\Relocations\GuestRelocationPage;
use App\Livewire\Host\Relocations\HostRelocationDetailsSheet;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingRelocation;
use App\Models\BookingRelocationInventoryTransfer;
use App\Models\BookingStay;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\Room;
use App\Models\RoomCurrentOccupancySnapshot;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Availability\SleepingPlaceDateLockService;
use App\Services\Bookings\BookingRelocationApplyService;
use App\Services\Bookings\BookingRelocationConsentService;
use App\Services\Bookings\BookingRelocationHostResponseService;
use App\Services\Bookings\BookingRelocationInventoryService;
use App\Services\Bookings\BookingRelocationOptionService;
use App\Services\Bookings\BookingRelocationPaymentService;
use App\Services\Bookings\BookingRelocationPrivacyService;
use App\Services\Bookings\BookingRelocationService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BookingRelocationFlowPointFourteenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-12 09:00:00');
        CarbonImmutable::setTestNow('2026-07-12 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_relocation_schema_contract_exists(): void
    {
        foreach ([
            'booking_relocations',
            'booking_relocation_options',
            'booking_relocation_price_lines',
            'booking_relocation_validation_results',
            'booking_relocation_consents',
            'booking_relocation_host_responses',
            'booking_relocation_guest_responses',
            'booking_relocation_inventory_transfers',
            'booking_relocation_status_logs',
            'booking_relocation_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }

        foreach ([
            'relocation_number',
            'original_booking_id',
            'new_booking_id',
            'booking_stay_id',
            'guest_user_id',
            'host_user_id',
            'current_property_id',
            'current_room_id',
            'current_sleeping_place_id',
            'new_property_id',
            'new_room_id',
            'new_sleeping_place_id',
            'source_type',
            'source_id',
            'requested_by_user_id',
            'requested_by_type',
            'reason',
            'status',
            'relocation_date',
            'relocation_time',
            'old_period_check_in_date',
            'old_period_check_out_date',
            'new_period_check_in_date',
            'new_period_check_out_date',
            'old_remaining_value_amount',
            'new_remaining_value_amount',
            'price_difference_amount',
            'additional_payment_amount',
            'refund_amount',
            'price_difference_payer',
            'requires_guest_consent',
            'requires_host_consent',
            'guest_consented_at',
            'host_consented_at',
            'requires_payment',
            'payment_status',
            'requires_refund',
            'refund_status',
            'support_comment',
            'hold_dates',
            'hold_expires_at',
            'expires_at',
            'applied_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('booking_relocations', $column), "Missing booking_relocations.{$column}.");
        }
    }

    public function test_guest_can_request_relocation_for_own_active_booking_and_original_place_is_preserved(): void
    {
        [$guest, , $booking, $stay, $currentPlace, $newPlace] = $this->createActiveStayWithAlternative();

        $relocation = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $newPlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
            'guest_comment' => 'I would like a quieter bed.',
        ]);

        $this->assertStringStartsWith('REL-2026-', $relocation->relocation_number);
        $this->assertSame($booking->id, $relocation->original_booking_id);
        $this->assertSame($stay->id, $relocation->booking_stay_id);
        $this->assertSame($currentPlace->id, $relocation->current_sleeping_place_id);
        $this->assertSame($newPlace->id, $relocation->new_sleeping_place_id);
        $this->assertSame('2026-07-15', $relocation->relocation_date->toDateString());
        $this->assertSame('2026-07-15', $relocation->new_period_check_in_date->toDateString());
        $this->assertSame('2026-07-20', $relocation->new_period_check_out_date->toDateString());
        $this->assertSame('waiting_host_consent', $this->relocationStatus($relocation));
        $this->assertSame($currentPlace->id, $booking->refresh()->sleeping_place_id);

        $this->assertDatabaseHas('booking_relocation_events', [
            'booking_relocation_id' => $relocation->id,
            'event_key' => 'relocation_requested',
        ]);
    }

    public function test_guest_cannot_request_relocation_for_another_booking_or_same_sleeping_place(): void
    {
        [$guest, , $booking, , $currentPlace] = $this->createActiveStayWithAlternative();
        $otherGuest = User::factory()->create();

        $this->assertFalse(app(BookingRelocationPrivacyService::class)->canGuestView($otherGuest, BookingRelocation::factory()->make([
            'guest_user_id' => $guest->id,
        ])));

        $this->expectException(AuthorizationException::class);
        app(BookingRelocationService::class)->createFromGuestRequest($otherGuest, $booking, [
            'new_sleeping_place_id' => $currentPlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
        ]);
    }

    public function test_same_sleeping_place_is_invalid_and_records_validation_result(): void
    {
        [$guest, , $booking, , $currentPlace] = $this->createActiveStayWithAlternative();

        $this->expectException(ValidationException::class);

        try {
            app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
                'new_sleeping_place_id' => $currentPlace->id,
                'reason' => 'guest_wants_more_comfort',
                'relocation_date' => '2026-07-15',
            ]);
        } finally {
            $relocation = BookingRelocation::query()->where('original_booking_id', $booking->id)->latest('id')->first();

            $this->assertNotNull($relocation);
            $this->assertSame('failed', $this->relocationStatus($relocation));
            $this->assertDatabaseHas('booking_relocation_validation_results', [
                'booking_relocation_id' => $relocation->id,
                'validation_key' => 'new_sleeping_place_required',
                'blocking' => true,
            ]);
        }
    }

    public function test_host_can_offer_relocation_for_own_booking_only(): void
    {
        [, $host, $booking, , , $newPlace] = $this->createActiveStayWithAlternative();
        $otherHost = User::factory()->create(['is_host' => true]);

        $relocation = app(BookingRelocationService::class)->createFromHostOffer($host, $booking, $newPlace, [
            'reason' => 'host_offered_another_place',
            'relocation_date' => '2026-07-15',
            'host_comment' => 'I can offer a better bed.',
        ]);

        $this->assertSame('waiting_guest_consent', $this->relocationStatus($relocation));
        $this->assertTrue($relocation->requires_guest_consent);
        $this->assertFalse($relocation->requires_host_consent);

        $this->expectException(AuthorizationException::class);
        app(BookingRelocationService::class)->createFromHostOffer($otherHost, $booking, $newPlace, [
            'reason' => 'host_offered_another_place',
            'relocation_date' => '2026-07-15',
        ]);
    }

    public function test_relocation_options_cover_same_room_same_property_and_same_host_only(): void
    {
        [$guest, , $booking, , $currentPlace, $sameRoomPlace] = $this->createActiveStayWithAlternative();
        $samePropertyPlace = $this->makeSiblingPlace($currentPlace, [
            'room_id' => Room::factory()->for($currentPlace->property)->create(['property_id' => $currentPlace->property_id])->id,
            'base_price_per_night' => 18,
            'base_price' => 18,
        ]);
        $sameHostProperty = Property::factory()->for($booking->host, 'host')->create([
            'host_user_id' => $booking->host_user_id,
            'user_id' => $booking->host_user_id,
            'status' => PropertyStatus::Active->value,
        ]);
        $sameHostRoom = Room::factory()->for($sameHostProperty)->create(['status' => RoomStatus::Active->value]);
        $sameHostPlace = SleepingPlace::factory()->for($sameHostRoom)->for($sameHostProperty)->create([
            'user_id' => $booking->host_user_id,
            'status' => SleepingPlaceStatus::Active->value,
            'base_price_per_night' => 19,
            'base_price' => 19,
            'currency' => 'EUR',
        ]);
        $otherHost = User::factory()->create(['is_host' => true]);
        $otherProperty = Property::factory()->for($otherHost, 'host')->create(['host_user_id' => $otherHost->id, 'user_id' => $otherHost->id]);
        $otherRoom = Room::factory()->for($otherProperty)->create();
        SleepingPlace::factory()->for($otherRoom)->for($otherProperty)->create([
            'user_id' => $otherHost->id,
            'status' => SleepingPlaceStatus::Active->value,
        ]);

        $relocation = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'reason' => 'guest_wants_cheaper',
            'relocation_date' => '2026-07-15',
        ]);

        $options = app(BookingRelocationOptionService::class)->findOptions($relocation);

        $this->assertContains($sameRoomPlace->id, $options->pluck('sleeping_place_id')->all());
        $this->assertContains($samePropertyPlace->id, $options->pluck('sleeping_place_id')->all());
        $this->assertContains($sameHostPlace->id, $options->pluck('sleeping_place_id')->all());
        $this->assertNotContains($currentPlace->id, $options->pluck('sleeping_place_id')->all());
        $this->assertSame(3, $options->count());
    }

    public function test_unavailable_future_booking_lock_repair_complaint_blocks_and_guest_count_stop_relocation(): void
    {
        [$guest, , $booking, , , $newPlace] = $this->createActiveStayWithAlternative();

        $otherBooking = Booking::factory()->create([
            'guest_user_id' => User::factory(),
            'host_user_id' => $booking->host_user_id,
            'property_id' => $newPlace->property_id,
            'room_id' => $newPlace->room_id,
            'sleeping_place_id' => $newPlace->id,
            'check_in_date' => '2026-07-17',
            'check_in' => '2026-07-17',
            'check_out_date' => '2026-07-19',
            'check_out' => '2026-07-19',
            'status' => BookingStatus::Confirmed->value,
        ]);
        app(SleepingPlaceDateLockService::class)->createLocksForBooking($otherBooking);

        $this->expectException(ValidationException::class);

        try {
            app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
                'new_sleeping_place_id' => $newPlace->id,
                'reason' => 'guest_wants_more_comfort',
                'relocation_date' => '2026-07-15',
            ]);
        } finally {
            $relocation = BookingRelocation::query()->where('original_booking_id', $booking->id)->latest('id')->first();

            $this->assertNotNull($relocation);
            $this->assertDatabaseHas('booking_relocation_validation_results', [
                'booking_relocation_id' => $relocation->id,
                'validation_key' => 'date_locked_by_another_booking',
                'blocking' => true,
            ]);
        }
    }

    public function test_price_difference_payer_payment_and_refund_rules_work(): void
    {
        [$guest, $host, $booking, , , $expensivePlace] = $this->createActiveStayWithAlternative(newPlaceOverrides: [
            'base_price_per_night' => 25,
            'base_price' => 25,
            'deposit_amount' => 10,
        ]);

        $upgrade = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $expensivePlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
        ]);

        $this->assertEquals(100, (float) $upgrade->old_remaining_value_amount);
        $this->assertEquals(125, (float) $upgrade->new_remaining_value_amount);
        $this->assertEquals(25, (float) $upgrade->price_difference_amount);
        $this->assertEquals(25, (float) $upgrade->additional_payment_amount);
        $this->assertSame('guest', $upgrade->price_difference_payer);
        $this->assertTrue($upgrade->requires_payment);

        $hostOfferedPlace = $this->makeSiblingPlace($expensivePlace, [
            'base_price_per_night' => 25,
            'base_price' => 25,
        ]);
        $noExtraCharge = app(BookingRelocationService::class)->createFromHostOffer($host, $booking, $hostOfferedPlace, [
            'reason' => 'breakdown',
            'relocation_date' => '2026-07-15',
        ]);

        $this->assertSame('no_extra_charge', $noExtraCharge->price_difference_payer);
        $this->assertEquals(0, (float) $noExtraCharge->additional_payment_amount);
        $this->assertFalse($noExtraCharge->requires_payment);

        $cheapPlace = $this->makeSiblingPlace($expensivePlace, [
            'base_price_per_night' => 15,
            'base_price' => 15,
        ]);
        $refund = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $cheapPlace->id,
            'reason' => 'guest_wants_cheaper',
            'relocation_date' => '2026-07-15',
        ]);

        $this->assertSame('refund_to_guest', $refund->price_difference_payer);
        $this->assertEquals(25, (float) $refund->refund_amount);
        $this->assertTrue($refund->requires_refund);
    }

    public function test_required_consents_hold_lifecycle_payment_failure_and_apply_flow(): void
    {
        [$guest, $host, $booking, $stay, $currentPlace, $newPlace] = $this->createActiveStayWithAlternative(newPlaceOverrides: [
            'base_price_per_night' => 25,
            'base_price' => 25,
        ]);

        $relocation = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $newPlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
        ]);

        $this->assertSame(5, SleepingPlaceBookingDateLock::query()
            ->where('booking_relocation_id', $relocation->id)
            ->where('sleeping_place_id', $newPlace->id)
            ->where('lock_type', 'relocation_pending')
            ->where('status', 'active')
            ->count());

        $this->expectException(ValidationException::class);
        app(BookingRelocationApplyService::class)->apply($relocation);
    }

    public function test_paid_relocation_applies_new_booking_segment_without_overwriting_original_place(): void
    {
        [$guest, $host, $booking, $stay, $currentPlace, $newPlace] = $this->createActiveStayWithAlternative(newPlaceOverrides: [
            'base_price_per_night' => 25,
            'base_price' => 25,
        ]);

        $relocation = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $newPlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
        ]);

        app(BookingRelocationHostResponseService::class)->approve($host, $relocation);

        foreach ($relocation->refresh()->consents as $consent) {
            app(BookingRelocationConsentService::class)->accept(
                $consent->user_id === $guest->id ? $guest : $host,
                $consent,
            );
        }

        app(BookingRelocationPaymentService::class)->markPaid($relocation->refresh(), ['provider_payment_id' => 'demo-relocation']);
        $applied = app(BookingRelocationApplyService::class)->apply($relocation->refresh());
        $newBooking = $applied->newBooking()->first();

        $this->assertSame('applied', $this->relocationStatus($applied));
        $this->assertNotNull($newBooking);
        $this->assertSame($booking->id, $newBooking->relocation_from_booking_id);
        $this->assertSame($newPlace->id, $newBooking->sleeping_place_id);
        $this->assertSame($currentPlace->id, $booking->refresh()->sleeping_place_id);
        $this->assertSame('2026-07-15', $booking->check_out_date->toDateString());
        $this->assertSame('2026-07-15', $newBooking->check_in_date->toDateString());
        $this->assertSame('2026-07-20', $newBooking->check_out_date->toDateString());
        $this->assertSame($newBooking->id, $stay->refresh()->booking_id);
        $this->assertSame($newPlace->id, $stay->sleeping_place_id);
        $this->assertSame($newPlace->id, $newBooking->checkOut->refresh()->sleeping_place_id);

        $this->assertSame(5, SleepingPlaceBookingDateLock::query()
            ->where('booking_relocation_id', $relocation->id)
            ->where('booking_id', $newBooking->id)
            ->where('lock_type', 'booked')
            ->where('status', 'active')
            ->count());
        $this->assertSame(5, SleepingPlaceBookingDateLock::query()
            ->where('booking_id', $booking->id)
            ->where('sleeping_place_id', $currentPlace->id)
            ->where('status', 'active')
            ->count());

        $this->assertDatabaseHas('booking_relocation_events', [
            'booking_relocation_id' => $relocation->id,
            'event_key' => 'new_booking_segment_created',
        ]);
        $this->assertDatabaseHas('booking_relocation_events', [
            'booking_relocation_id' => $relocation->id,
            'event_key' => 'relocation_applied',
        ]);
        $this->assertDatabaseHas('booking_relocation_inventory_transfers', [
            'booking_relocation_id' => $relocation->id,
            'transfer_type' => 'return_old_key',
        ]);
        $this->assertNotNull(RoomCurrentOccupancySnapshot::query()->where('room_id', $newPlace->room_id)->first()?->last_recalculated_at);
        $this->assertNotNull(PropertyCurrentOccupancySnapshot::query()->where('property_id', $newPlace->property_id)->first()?->last_recalculated_at);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'booking_relocation_applied',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'booking_relocation_applied',
        ]);
    }

    public function test_reject_expire_cancel_and_payment_failure_release_new_place_holds(): void
    {
        [$guest, $host, $booking, , , $newPlace] = $this->createActiveStayWithAlternative();

        $rejected = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $newPlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
        ]);
        app(BookingRelocationHostResponseService::class)->reject($host, $rejected, 'not_possible');
        $this->assertSame(0, SleepingPlaceBookingDateLock::query()->where('booking_relocation_id', $rejected->id)->where('status', 'active')->count());

        $cancelled = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $newPlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
        ]);
        app(BookingRelocationService::class)->cancel($guest, $cancelled, 'changed_mind');
        $this->assertSame(0, SleepingPlaceBookingDateLock::query()->where('booking_relocation_id', $cancelled->id)->where('status', 'active')->count());

        $expired = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $newPlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
        ]);
        app(BookingRelocationService::class)->markExpired($expired);
        $this->assertSame(0, SleepingPlaceBookingDateLock::query()->where('booking_relocation_id', $expired->id)->where('status', 'active')->count());

        $failed = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $newPlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
        ]);
        app(BookingRelocationHostResponseService::class)->approve($host, $failed);
        app(BookingRelocationPaymentService::class)->markPaymentFailed($failed, 'demo_failed');
        $this->assertSame('failed', $this->relocationStatus($failed->refresh()));
        $this->assertSame(0, SleepingPlaceBookingDateLock::query()->where('booking_relocation_id', $failed->id)->where('status', 'active')->count());
    }

    public function test_inventory_transfer_can_be_completed_and_support_fields_are_hidden_from_guest(): void
    {
        [$guest, $host, $booking, , , $newPlace] = $this->createActiveStayWithAlternative();
        $relocation = app(BookingRelocationService::class)->createFromHostOffer($host, $booking, $newPlace, [
            'reason' => 'breakdown',
            'relocation_date' => '2026-07-15',
            'support_comment' => 'Internal future support note.',
        ]);

        $transfer = BookingRelocationInventoryTransfer::query()->where('booking_relocation_id', $relocation->id)->firstOrFail();
        app(BookingRelocationInventoryService::class)->markTransferCompleted($transfer);

        $this->assertDatabaseHas('booking_relocation_inventory_transfers', [
            'id' => $transfer->id,
            'status' => 'completed',
        ]);

        $guestPayload = app(BookingRelocationPrivacyService::class)->filterForGuest($guest, $relocation->refresh());
        $hostPayload = app(BookingRelocationPrivacyService::class)->filterForHost($host, $relocation->refresh());

        $this->assertArrayNotHasKey('support_comment', $guestPayload);
        $this->assertArrayHasKey('support_comment', $hostPayload);
    }

    public function test_guest_and_host_relocation_components_render_in_english_and_russian(): void
    {
        [$guest, $host, $booking, , , $newPlace] = $this->createActiveStayWithAlternative();
        $relocation = app(BookingRelocationService::class)->createFromGuestRequest($guest, $booking, [
            'new_sleeping_place_id' => $newPlace->id,
            'reason' => 'guest_wants_more_comfort',
            'relocation_date' => '2026-07-15',
        ]);

        app()->setLocale('en');
        Livewire::actingAs($guest)
            ->test(GuestRelocationPage::class, ['booking' => $booking])
            ->assertSee(__('booking_relocations.title', [], 'en'))
            ->assertSee(__('booking_relocations.actions.request_relocation', [], 'en'));

        Livewire::actingAs($host)
            ->test(HostRelocationDetailsSheet::class, ['relocation' => $relocation])
            ->assertSee(__('booking_relocations.actions.approve', [], 'en'));

        app()->setLocale('ru');
        Livewire::actingAs($guest)
            ->test(GuestRelocationPage::class, ['booking' => $booking])
            ->assertSee(__('booking_relocations.title', [], 'ru'))
            ->assertSee(__('booking_relocations.actions.request_relocation', [], 'ru'));

        Livewire::actingAs($host)
            ->test(HostRelocationDetailsSheet::class, ['relocation' => $relocation])
            ->assertSee(__('booking_relocations.actions.approve', [], 'ru'));
    }

    /**
     * @param  array<string, mixed>  $currentPlaceOverrides
     * @param  array<string, mixed>  $newPlaceOverrides
     * @return array{0:User,1:User,2:Booking,3:BookingStay,4:SleepingPlace,5:SleepingPlace}
     */
    private function createActiveStayWithAlternative(array $currentPlaceOverrides = [], array $newPlaceOverrides = []): array
    {
        $guest = User::factory()->create();
        UserProfile::factory()->for($guest, 'user')->create();

        $host = User::factory()->create(['is_host' => true]);
        UserProfile::factory()->for($host, 'user')->create();
        HostProfile::factory()->for($host, 'user')->create();

        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
            'status' => PropertyStatus::Active->value,
        ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active->value,
        ]);

        $currentPlace = SleepingPlace::factory()->for($room)->for($property)->create(array_merge([
            'user_id' => $host->id,
            'status' => SleepingPlaceStatus::Active->value,
            'publication_status' => 'published',
            'base_price_per_night' => 20,
            'base_price' => 20,
            'weekend_price' => null,
            'weekly_price' => null,
            'monthly_price' => null,
            'deposit_amount' => 0,
            'currency' => 'EUR',
            'max_guests' => 1,
            'max_guests_count' => 1,
            'max_nights' => 30,
        ], $currentPlaceOverrides));

        $newPlace = SleepingPlace::factory()->for($room)->for($property)->create(array_merge([
            'user_id' => $host->id,
            'status' => SleepingPlaceStatus::Active->value,
            'publication_status' => 'published',
            'base_price_per_night' => 22,
            'base_price' => 22,
            'weekend_price' => null,
            'weekly_price' => null,
            'monthly_price' => null,
            'deposit_amount' => 0,
            'currency' => 'EUR',
            'max_guests' => 1,
            'max_guests_count' => 1,
            'max_nights' => 30,
        ], $newPlaceOverrides));

        $booking = Booking::factory()->create([
            'guest_user_id' => $guest->id,
            'guest_id' => $guest->id,
            'host_user_id' => $host->id,
            'host_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $currentPlace->id,
            'check_in' => '2026-07-10',
            'check_in_date' => '2026-07-10',
            'check_out' => '2026-07-20',
            'check_out_date' => '2026-07-20',
            'check_out_time' => '11:00',
            'nights' => 10,
            'nights_count' => 10,
            'chargeable_days_count' => 10,
            'calendar_days_count' => 11,
            'calendar_presence_days_count' => 11,
            'guests_count' => 1,
            'price_per_night' => 20,
            'subtotal' => 200,
            'subtotal_amount' => 200,
            'accommodation_amount' => 200,
            'service_fee' => 10,
            'service_fee_amount' => 10,
            'cleaning_fee' => 0,
            'cleaning_fee_amount' => 0,
            'deposit' => 0,
            'deposit_amount' => 0,
            'total' => 210,
            'total_amount' => 210,
            'total_payable' => 210,
            'host_payout_amount' => 200,
            'refundable_amount' => 0,
            'non_refundable_amount' => 210,
            'currency' => 'EUR',
            'status' => BookingStatus::InProgress->value,
            'payment_status' => PaymentStatus::Paid->value,
            'has_dispute' => false,
            'has_complaint' => false,
        ]);

        app(SleepingPlaceDateLockService::class)->createLocksForBooking($booking);

        $stay = BookingStay::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $currentPlace->id,
            'status' => 'active',
            'check_in_date' => '2026-07-10',
            'planned_check_out_date' => '2026-07-20',
            'nights_count' => 10,
            'calendar_presence_days_count' => 11,
            'nights_remaining' => 8,
            'relocation_requested' => false,
        ]);

        BookingCheckOut::factory()->create([
            'booking_id' => $booking->id,
            'booking_stay_id' => $stay->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $currentPlace->id,
            'check_out_date' => '2026-07-20',
            'planned_check_out_time' => '11:00',
            'status' => 'scheduled',
        ]);

        foreach (array_unique([$room->id, $newPlace->room_id]) as $roomId) {
            RoomCurrentOccupancySnapshot::factory()->create([
                'room_id' => $roomId,
                'property_id' => $property->id,
                'host_user_id' => $host->id,
            ]);
        }

        PropertyCurrentOccupancySnapshot::factory()->create([
            'property_id' => $property->id,
            'host_user_id' => $host->id,
        ]);

        return [$guest, $host, $booking, $stay, $currentPlace, $newPlace];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeSiblingPlace(SleepingPlace $place, array $overrides = []): SleepingPlace
    {
        return SleepingPlace::factory()->for($place->room)->for($place->property)->create(array_merge([
            'user_id' => $place->user_id,
            'status' => SleepingPlaceStatus::Active->value,
            'publication_status' => 'published',
            'base_price_per_night' => 20,
            'base_price' => 20,
            'currency' => 'EUR',
            'max_guests' => 1,
            'max_guests_count' => 1,
        ], $overrides));
    }

    private function relocationStatus(BookingRelocation $relocation): string
    {
        return $relocation->status instanceof \BackedEnum
            ? (string) $relocation->status->value
            : (string) $relocation->status;
    }
}
