<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Bookings\Extensions\GuestExtensionPage;
use App\Livewire\Host\Extensions\HostExtensionDetailsSheet;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingExtension;
use App\Models\BookingStay;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\Room;
use App\Models\RoomCurrentOccupancySnapshot;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\SleepingPlaceCalendarBlock;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Bookings\BookingExtensionApplyService;
use App\Services\Bookings\BookingExtensionGuestResponseService;
use App\Services\Bookings\BookingExtensionHostResponseService;
use App\Services\Bookings\BookingExtensionPaymentService;
use App\Services\Bookings\BookingExtensionPrivacyService;
use App\Services\Bookings\BookingExtensionService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BookingExtensionFlowPointThirteenTest extends TestCase
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

    public function test_extension_schema_contract_exists(): void
    {
        foreach ([
            'booking_extensions',
            'booking_extension_lines',
            'booking_extension_validation_results',
            'booking_extension_host_responses',
            'booking_extension_guest_responses',
            'booking_extension_status_logs',
            'booking_extension_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }

        foreach ([
            'extension_number',
            'booking_stay_id',
            'guest_user_id',
            'host_user_id',
            'property_id',
            'room_id',
            'sleeping_place_id',
            'booking_payment_id',
            'current_check_out_date',
            'new_check_out_date',
            'additional_nights_count',
            'additional_chargeable_days_count',
            'additional_calendar_presence_days_count',
            'extension_type',
            'requires_host_confirmation',
            'requires_payment',
            'payment_status',
            'accommodation_amount',
            'service_fee_amount',
            'additional_deposit_amount',
            'total_payable',
            'host_payout_amount',
            'hold_dates',
            'hold_expires_at',
            'expires_at',
            'applied_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('booking_extensions', $column), "Missing booking_extensions.{$column}.");
        }
    }

    public function test_guest_can_request_extension_for_own_booking_and_same_sleeping_place(): void
    {
        [$guest, , $booking, $stay, $place] = $this->createActiveStay();

        $extension = app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-18',
            'guest_message' => 'I would like to stay longer.',
        ]);

        $this->assertStringStartsWith('EXT-2026-', $extension->extension_number);
        $this->assertSame($booking->id, $extension->booking_id);
        $this->assertSame($stay->id, $extension->booking_stay_id);
        $this->assertSame($place->id, $extension->sleeping_place_id);
        $this->assertSame('2026-07-15', $extension->current_check_out_date->toDateString());
        $this->assertSame('2026-07-18', $extension->new_check_out_date->toDateString());
        $this->assertSame(3, $extension->additional_nights_count);
        $this->assertSame(3, $extension->additional_chargeable_days_count);
        $this->assertSame(4, $extension->additional_calendar_presence_days_count);
        $this->assertSame('waiting_host_confirmation', $this->extensionStatus($extension));

        $this->assertDatabaseHas('booking_extension_events', [
            'booking_extension_id' => $extension->id,
            'event_key' => 'extension_requested',
        ]);
    }

    public function test_guest_cannot_request_extension_for_another_booking_and_invalid_dates_are_recorded(): void
    {
        [$guest, , $booking] = $this->createActiveStay();
        $otherGuest = User::factory()->create();

        $this->assertFalse(app(BookingExtensionPrivacyService::class)->canGuestCreate($otherGuest, $booking));

        $this->expectException(ValidationException::class);

        try {
            app(BookingExtensionService::class)->createRequest($guest, $booking, [
                'new_check_out_date' => '2026-07-15',
            ]);
        } finally {
            $extension = BookingExtension::query()->where('booking_id', $booking->id)->latest('id')->first();

            $this->assertNotNull($extension);
            $this->assertSame('availability_check_failed', $this->extensionStatus($extension));
            $this->assertDatabaseHas('booking_extension_validation_results', [
                'booking_extension_id' => $extension->id,
                'validation_key' => 'new_checkout_same_as_current_checkout',
                'blocking' => true,
            ]);
        }
    }

    public function test_extension_checks_only_additional_period_and_blocks_conflicts_after_checkout(): void
    {
        [$guest, , $booking, , $place] = $this->createActiveStay();

        SleepingPlaceBookingDateLock::factory()->create([
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
            'date' => '2026-07-14',
            'lock_type' => 'booked',
            'status' => 'active',
        ]);

        $extension = app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-16',
        ]);

        $this->assertSame(1, $extension->additional_nights_count);
        $this->assertDatabaseHas('booking_extension_lines', [
            'booking_extension_id' => $extension->id,
            'line_type' => 'extension_night',
            'date' => '2026-07-15',
        ]);

        $otherBooking = Booking::factory()->create([
            'guest_user_id' => User::factory(),
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'check_in' => '2026-07-16',
            'check_in_date' => '2026-07-16',
            'check_out' => '2026-07-19',
            'check_out_date' => '2026-07-19',
            'status' => BookingStatus::Confirmed->value,
        ]);

        SleepingPlaceBookingDateLock::factory()->create([
            'sleeping_place_id' => $place->id,
            'booking_id' => $otherBooking->id,
            'date' => '2026-07-16',
            'lock_type' => 'booked',
            'status' => 'active',
        ]);

        $this->expectException(ValidationException::class);

        app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-18',
        ]);
    }

    public function test_repair_complaint_room_property_blocks_and_rules_can_stop_extension(): void
    {
        [$guest, , $booking, , $place] = $this->createActiveStay();

        SleepingPlaceCalendarBlock::factory()->repair()->create([
            'sleeping_place_id' => $place->id,
            'room_id' => $booking->room_id,
            'property_id' => $booking->property_id,
            'starts_at' => '2026-07-16',
            'ends_at' => '2026-07-17',
            'check_in_date' => '2026-07-16',
            'check_out_date' => '2026-07-17',
        ]);

        $this->expectException(ValidationException::class);

        app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-18',
        ]);
    }

    public function test_temporary_holds_are_created_only_for_extension_dates_and_released_on_reject_cancel_or_payment_failure(): void
    {
        [$guest, $host, $booking] = $this->createActiveStay();

        $extension = app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-18',
        ]);

        $this->assertSame(3, SleepingPlaceBookingDateLock::query()
            ->where('booking_extension_id', $extension->id)
            ->where('lock_type', 'extension_pending')
            ->where('status', 'active')
            ->count());
        $this->assertDatabaseMissing('sleeping_place_booking_date_locks', [
            'booking_extension_id' => $extension->id,
            'date' => '2026-07-14',
        ]);

        app(BookingExtensionHostResponseService::class)->reject($host, $extension, 'calendar_changed');

        $this->assertSame(0, SleepingPlaceBookingDateLock::query()
            ->where('booking_extension_id', $extension->id)
            ->where('status', 'active')
            ->count());

        $cancelled = app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-17',
        ]);
        app(BookingExtensionService::class)->cancelByGuest($guest, $cancelled);
        $this->assertSame(0, SleepingPlaceBookingDateLock::query()->where('booking_extension_id', $cancelled->id)->where('status', 'active')->count());

        $failedPayment = app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-17',
        ]);
        app(BookingExtensionHostResponseService::class)->approve($host, $failedPayment);
        app(BookingExtensionPaymentService::class)->markPaymentFailed($failedPayment, 'demo_failed');
        $this->assertSame('payment_failed', $this->extensionStatus($failedPayment->refresh()));
        $this->assertSame(0, SleepingPlaceBookingDateLock::query()->where('booking_extension_id', $failedPayment->id)->where('status', 'active')->count());
    }

    public function test_paid_extension_applies_to_booking_stay_checkout_locks_snapshots_and_notifications(): void
    {
        [$guest, $host, $booking, $stay] = $this->createActiveStay(placeOverrides: [
            'requires_host_approval' => true,
            'instant_booking_enabled' => false,
        ]);

        $extension = app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-18',
        ]);

        app(BookingExtensionHostResponseService::class)->approve($host, $extension, 'Approved.');
        $extension = app(BookingExtensionPaymentService::class)->markPaid($extension, ['provider_payment_id' => 'demo-extension']);
        app(BookingExtensionApplyService::class)->apply($extension);

        $booking->refresh();
        $stay->refresh();

        $this->assertSame('applied', $this->extensionStatus($extension->refresh()));
        $this->assertSame('2026-07-18', $booking->check_out_date->toDateString());
        $this->assertSame(8, $booking->nights_count);
        $this->assertSame('2026-07-18', $stay->planned_check_out_date->toDateString());
        $this->assertFalse($stay->extension_requested);
        $this->assertSame('active', $stay->status);
        $this->assertSame('2026-07-18', $booking->checkOut->refresh()->check_out_date->toDateString());

        $this->assertSame(3, SleepingPlaceBookingDateLock::query()
            ->where('booking_extension_id', $extension->id)
            ->where('booking_id', $booking->id)
            ->where('lock_type', 'booked')
            ->where('status', 'active')
            ->count());

        $this->assertNotNull(RoomCurrentOccupancySnapshot::query()->where('room_id', $booking->room_id)->first()?->last_recalculated_at);
        $this->assertNotNull(PropertyCurrentOccupancySnapshot::query()->where('property_id', $booking->property_id)->first()?->last_recalculated_at);

        $this->assertDatabaseHas('booking_extension_events', [
            'booking_extension_id' => $extension->id,
            'event_key' => 'extension_applied',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'booking_extension_applied',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'booking_extension_applied',
        ]);
    }

    public function test_host_proposal_and_guest_acceptance_recalculates_checkout(): void
    {
        [$guest, $host, $booking] = $this->createActiveStay();

        $extension = app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-20',
        ]);

        app(BookingExtensionHostResponseService::class)->proposeNewCheckout($host, $extension, [
            'proposed_new_check_out_date' => '2026-07-18',
            'message' => 'I can offer three extra nights.',
        ]);

        app(BookingExtensionGuestResponseService::class)->acceptHostProposal($guest, $extension, [
            'accepted_new_check_out_date' => '2026-07-18',
        ]);

        $extension->refresh();

        $this->assertSame('2026-07-18', $extension->new_check_out_date->toDateString());
        $this->assertSame(3, $extension->additional_nights_count);
        $this->assertSame('approved_waiting_payment', $this->extensionStatus($extension));
    }

    public function test_guest_and_host_extension_components_render_in_english_and_russian(): void
    {
        [$guest, $host, $booking] = $this->createActiveStay();
        $extension = app(BookingExtensionService::class)->createRequest($guest, $booking, [
            'new_check_out_date' => '2026-07-18',
        ]);

        app()->setLocale('en');
        Livewire::actingAs($guest)
            ->test(GuestExtensionPage::class, ['booking' => $booking])
            ->assertSee(__('booking_extensions.title', [], 'en'))
            ->assertSee(__('booking_extensions.actions.request_extension', [], 'en'));

        Livewire::actingAs($host)
            ->test(HostExtensionDetailsSheet::class, ['extension' => $extension])
            ->assertSee(__('booking_extensions.actions.approve', [], 'en'));

        app()->setLocale('ru');
        Livewire::actingAs($guest)
            ->test(GuestExtensionPage::class, ['booking' => $booking])
            ->assertSee(__('booking_extensions.title', [], 'ru'))
            ->assertSee(__('booking_extensions.actions.request_extension', [], 'ru'));

        Livewire::actingAs($host)
            ->test(HostExtensionDetailsSheet::class, ['extension' => $extension])
            ->assertSee(__('booking_extensions.actions.approve', [], 'ru'));
    }

    /**
     * @param  array<string, mixed>  $placeOverrides
     * @return array{0:User,1:User,2:Booking,3:BookingStay,4:SleepingPlace}
     */
    private function createActiveStay(array $placeOverrides = []): array
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

        $place = SleepingPlace::factory()->for($room)->for($property)->create(array_merge([
            'status' => SleepingPlaceStatus::Active->value,
            'base_price_per_night' => 20,
            'base_price' => 20,
            'weekend_price' => null,
            'weekly_price' => null,
            'monthly_price' => null,
            'cleaning_fee' => 0,
            'deposit_amount' => 0,
            'currency' => 'EUR',
            'max_nights' => 30,
            'max_guests' => 1,
            'max_guests_count' => 1,
            'extensions_allowed' => true,
            'can_extend' => true,
            'requires_host_approval' => true,
            'instant_booking_enabled' => false,
        ], $placeOverrides));

        $booking = Booking::factory()->create([
            'guest_user_id' => $guest->id,
            'guest_id' => $guest->id,
            'host_user_id' => $host->id,
            'host_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_in' => '2026-07-10',
            'check_in_date' => '2026-07-10',
            'check_out' => '2026-07-15',
            'check_out_date' => '2026-07-15',
            'check_out_time' => '11:00',
            'nights' => 5,
            'nights_count' => 5,
            'chargeable_days_count' => 5,
            'calendar_days_count' => 6,
            'calendar_presence_days_count' => 6,
            'guests_count' => 1,
            'price_per_night' => 20,
            'subtotal' => 100,
            'subtotal_amount' => 100,
            'accommodation_amount' => 100,
            'service_fee' => 5,
            'service_fee_amount' => 5,
            'cleaning_fee' => 0,
            'cleaning_fee_amount' => 0,
            'deposit' => 0,
            'deposit_amount' => 0,
            'total' => 105,
            'total_amount' => 105,
            'total_payable' => 105,
            'host_payout_amount' => 100,
            'refundable_amount' => 0,
            'non_refundable_amount' => 105,
            'currency' => 'EUR',
            'status' => BookingStatus::InProgress->value,
            'payment_status' => PaymentStatus::Paid->value,
            'has_dispute' => false,
            'has_complaint' => false,
        ]);

        $stay = BookingStay::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => 'checkout_soon',
            'check_in_date' => '2026-07-10',
            'planned_check_out_date' => '2026-07-15',
            'nights_count' => 5,
            'calendar_presence_days_count' => 6,
            'nights_remaining' => 3,
            'extension_requested' => true,
            'checkout_soon' => true,
        ]);

        BookingCheckOut::factory()->create([
            'booking_id' => $booking->id,
            'booking_stay_id' => $stay->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_out_date' => '2026-07-15',
            'planned_check_out_time' => '11:00',
            'status' => 'checkout_soon',
        ]);

        RoomCurrentOccupancySnapshot::factory()->create([
            'room_id' => $room->id,
            'property_id' => $property->id,
            'host_user_id' => $host->id,
        ]);

        PropertyCurrentOccupancySnapshot::factory()->create([
            'property_id' => $property->id,
            'host_user_id' => $host->id,
        ]);

        return [$guest, $host, $booking, $stay, $place];
    }

    private function extensionStatus(BookingExtension $extension): string
    {
        return $extension->status instanceof \BackedEnum
            ? (string) $extension->status->value
            : (string) $extension->status;
    }
}
