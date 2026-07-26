<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\Cancellations\CancellationPreviewCard;
use App\Livewire\Bookings\Cancellations\GuestCancellationPage;
use App\Livewire\Host\Cancellations\HostCancellationDetailsSheet;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\User;
use App\Services\Bookings\BookingCancellationPreviewService;
use App\Services\Bookings\BookingCancellationPrivacyService;
use App\Services\Bookings\BookingCancellationService;
use App\Services\Bookings\CancellationPolicyService;
use App\Services\Bookings\CancellationPolicySnapshotService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BookingCancellationFlowPointFifteenTest extends TestCase
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

    public function test_point_fifteen_schema_exists_with_core_indexes(): void
    {
        foreach ([
            'sleeping_place_cancellation_policies',
            'sleeping_place_cancellation_policy_rules',
            'booking_cancellation_policy_snapshots',
            'booking_cancellation_previews',
            'booking_cancellations',
            'booking_cancellation_refund_lines',
            'booking_cancellation_status_logs',
            'booking_cancellation_events',
            'booking_cancellation_alternatives',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table.' table is missing.');
        }

        $this->assertTrue(Schema::hasColumn('booking_cancellations', 'calendar_release_status'));
        $this->assertTrue(Schema::hasColumn('booking_cancellation_previews', 'policy_snapshot_json'));
        $this->assertTrue(Schema::hasColumn('booking_cancellation_policy_snapshots', 'rules_snapshot_json'));
    }

    public function test_policy_snapshot_and_preview_use_booking_snapshot_not_current_place_policy(): void
    {
        [$guest, $host, $place, $booking] = $this->createPaidBooking();

        $policy = app(CancellationPolicyService::class)->updateForSleepingPlace($host, $place, [
            'policy_type' => 'flexible',
            'title' => 'Flexible booking terms',
            'free_cancellation_until_hours_before_check_in' => 24,
        ]);

        $snapshot = app(CancellationPolicySnapshotService::class)->createForBooking($booking);

        $policy->forceFill([
            'policy_type' => 'non_refundable',
            'title' => 'Changed after booking',
        ])->save();

        $preview = app(BookingCancellationPreviewService::class)->createPreview($guest, $booking, [
            'cancellation_type' => 'guest_fault',
            'reason_key' => 'changed_plans',
        ]);

        $this->assertSame('flexible', $snapshot->policy_type);
        $this->assertSame('flexible', $preview->policy_snapshot_json['policy_type']);
        $this->assertSame(175.0, (float) $preview->total_refund_amount);
        $this->assertSame(0.0, (float) $preview->total_non_refundable_amount);
    }

    public function test_paid_booking_cannot_be_cancelled_without_preview(): void
    {
        [$guest, , , $booking] = $this->createPaidBooking();

        app(CancellationPolicySnapshotService::class)->createForBooking($booking);

        $this->expectException(ValidationException::class);

        app(BookingCancellationService::class)->cancelBooking($guest, $booking, [
            'cancellation_type' => 'guest_fault',
            'reason_key' => 'changed_plans',
        ]);
    }

    public function test_confirmed_cancellation_creates_refund_lines_updates_booking_and_releases_locks_before_check_in(): void
    {
        [$guest, , $place, $booking] = $this->createPaidBooking();

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

        $preview = app(BookingCancellationPreviewService::class)->createPreview($guest, $booking, [
            'cancellation_type' => 'guest_fault',
            'reason_key' => 'changed_plans',
        ]);

        $cancellation = app(BookingCancellationService::class)->confirmCancellation($guest, $preview);

        $this->assertSame('booking_cancelled', $cancellation->status);
        $this->assertSame('released', $cancellation->calendar_release_status);
        $this->assertSame(175.0, (float) $cancellation->total_refund_amount);
        $this->assertSame(BookingStatus::CancelledByGuestFlow, $booking->fresh()->status);

        $this->assertDatabaseHas('booking_refunds', [
            'booking_id' => $booking->id,
            'source_type' => 'booking_cancellation',
            'source_id' => $cancellation->id,
            'amount' => 175,
        ]);
        $this->assertDatabaseHas('booking_cancellation_refund_lines', [
            'booking_cancellation_id' => $cancellation->id,
            'line_type' => 'accommodation',
            'refund_amount' => 100,
        ]);
        $this->assertDatabaseHas('booking_cancellation_refund_lines', [
            'booking_cancellation_id' => $cancellation->id,
            'line_type' => 'deposit',
            'refund_amount' => 50,
        ]);
        $this->assertDatabaseMissing('sleeping_place_booking_date_locks', [
            'booking_id' => $booking->id,
            'status' => 'active',
        ]);
    }

    public function test_non_refundable_policy_returns_deposit_separately_before_check_in(): void
    {
        [$guest, $host, $place, $booking] = $this->createPaidBooking([
            'cancellation_policy' => CancellationPolicy::NonRefundable,
            'free_cancel_before' => null,
        ]);

        app(CancellationPolicyService::class)->updateForSleepingPlace($host, $place, [
            'policy_type' => 'non_refundable',
            'title' => 'Non refundable',
        ]);
        app(CancellationPolicySnapshotService::class)->createForBooking($booking);

        $preview = app(BookingCancellationPreviewService::class)->createPreview($guest, $booking, [
            'cancellation_type' => 'non_refundable',
            'reason_key' => 'changed_plans',
        ]);

        $this->assertSame(0.0, (float) $preview->accommodation_refund_amount);
        $this->assertSame(50.0, (float) $preview->deposit_refund_amount);
        $this->assertGreaterThanOrEqual(50.0, (float) $preview->total_refund_amount);
    }

    public function test_host_cancellation_gives_guest_full_refund(): void
    {
        [, $host, , $booking] = $this->createPaidBooking([
            'free_cancel_before' => null,
        ]);

        app(CancellationPolicySnapshotService::class)->createForBooking($booking);

        $preview = app(BookingCancellationPreviewService::class)->createPreview($host, $booking, [
            'cancellation_type' => 'host_fault',
            'reason_key' => 'maintenance',
            'requested_by_type' => 'host',
        ]);
        $cancellation = app(BookingCancellationService::class)->confirmCancellation($host, $preview);

        $this->assertSame('host', $cancellation->cancelled_by_type);
        $this->assertSame(175.0, (float) $cancellation->total_refund_amount);
        $this->assertSame(0.0, (float) $cancellation->total_non_refundable_amount);
        $this->assertSame(BookingStatus::CancelledByHostFlow, $booking->fresh()->status);
    }

    public function test_after_check_in_cancellation_keeps_calendar_blocked_until_checkout_flow(): void
    {
        Carbon::setTestNow('2026-07-11 10:00:00');
        CarbonImmutable::setTestNow('2026-07-11 10:00:00');

        [$guest, , $place, $booking] = $this->createPaidBooking([
            'status' => BookingStatus::GuestCheckedIn,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-13',
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-13',
            'nights_count' => 3,
            'nights' => 3,
            'guest_checked_in_at' => '2026-07-10 15:00:00',
        ]);

        app(CancellationPolicySnapshotService::class)->createForBooking($booking);

        foreach (['2026-07-10', '2026-07-11', '2026-07-12'] as $date) {
            SleepingPlaceBookingDateLock::factory()->create([
                'sleeping_place_id' => $place->id,
                'booking_id' => $booking->id,
                'date' => $date,
                'lock_type' => 'booked',
                'status' => 'active',
            ]);
        }

        $preview = app(BookingCancellationPreviewService::class)->createPreview($guest, $booking, [
            'cancellation_type' => 'early_termination',
            'reason_key' => 'housing_problem',
        ]);
        $cancellation = app(BookingCancellationService::class)->confirmCancellation($guest, $preview);

        $this->assertSame(1, $cancellation->nights_used);
        $this->assertSame(2, $cancellation->nights_unused);
        $this->assertSame('kept_blocked', $cancellation->calendar_release_status);
        $this->assertSame(3, SleepingPlaceBookingDateLock::query()->where('booking_id', $booking->id)->where('status', 'active')->count());
    }

    public function test_cancellation_privacy_filters_hide_internal_payout_from_guest(): void
    {
        [$guest, $host, , $booking] = $this->createPaidBooking();
        $otherGuest = User::factory()->create();
        $otherHost = User::factory()->host()->create();

        app(CancellationPolicySnapshotService::class)->createForBooking($booking);

        $preview = app(BookingCancellationPreviewService::class)->createPreview($guest, $booking, [
            'cancellation_type' => 'guest_fault',
            'reason_key' => 'changed_plans',
        ]);
        $cancellation = app(BookingCancellationService::class)->confirmCancellation($guest, $preview);

        $privacy = app(BookingCancellationPrivacyService::class);

        $this->assertTrue($privacy->canGuestView($guest, $cancellation));
        $this->assertFalse($privacy->canGuestView($otherGuest, $cancellation));
        $this->assertTrue($privacy->canHostView($host, $cancellation));
        $this->assertFalse($privacy->canHostView($otherHost, $cancellation));
        $this->assertArrayNotHasKey('host_payout_adjustment_amount', $privacy->filterForGuest($guest, $cancellation));
        $this->assertArrayHasKey('host_payout_adjustment_amount', $privacy->filterForHost($host, $cancellation));
    }

    public function test_cancellation_preview_and_host_details_render_in_english_and_russian(): void
    {
        [$guest, $host, , $booking] = $this->createPaidBooking();

        app(CancellationPolicySnapshotService::class)->createForBooking($booking);

        $preview = app(BookingCancellationPreviewService::class)->createPreview($guest, $booking, [
            'cancellation_type' => 'guest_fault',
            'reason_key' => 'changed_plans',
        ]);
        $cancellation = app(BookingCancellationService::class)->confirmCancellation($guest, $preview);

        app()->setLocale('en');

        Livewire::actingAs($guest)
            ->test(CancellationPreviewCard::class, ['preview' => $preview])
            ->assertSee(__('cancellations.title'))
            ->assertSee(__('cancellations.actions.confirm_cancellation'));

        Livewire::actingAs($host)
            ->test(HostCancellationDetailsSheet::class, ['cancellation' => $cancellation])
            ->assertSee(__('cancellations.host_title'))
            ->assertSee(__('cancellations.fields.total_refund'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(CancellationPreviewCard::class, ['preview' => $preview])
            ->assertSee(__('cancellations.title'))
            ->assertSee(__('cancellations.actions.confirm_cancellation'));

        Livewire::actingAs($host)
            ->test(HostCancellationDetailsSheet::class, ['cancellation' => $cancellation])
            ->assertSee(__('cancellations.host_title'))
            ->assertSee(__('cancellations.fields.total_refund'));
    }

    public function test_host_cancellation_sheet_rejects_cancellation_owned_by_another_host(): void
    {
        [, $host] = $this->createPaidBooking();
        [$otherGuest, , , $otherBooking] = $this->createPaidBooking();

        app(CancellationPolicySnapshotService::class)->createForBooking($otherBooking);

        $preview = app(BookingCancellationPreviewService::class)->createPreview($otherGuest, $otherBooking, [
            'cancellation_type' => 'guest_fault',
            'reason_key' => 'changed_plans',
        ]);
        $cancellation = app(BookingCancellationService::class)->confirmCancellation($otherGuest, $preview);

        Livewire::actingAs($host)
            ->test(HostCancellationDetailsSheet::class, ['cancellation' => $cancellation])
            ->assertForbidden();
    }

    public function test_guest_cancellation_page_rejects_context_owned_by_another_guest(): void
    {
        [$guest] = $this->createPaidBooking();
        [$otherGuest, , , $otherBooking] = $this->createPaidBooking();

        app(CancellationPolicySnapshotService::class)->createForBooking($otherBooking);

        $preview = app(BookingCancellationPreviewService::class)->createPreview($otherGuest, $otherBooking, [
            'cancellation_type' => 'guest_fault',
            'reason_key' => 'changed_plans',
        ]);
        $cancellation = app(BookingCancellationService::class)->confirmCancellation($otherGuest, $preview);

        foreach ([
            ['booking' => $otherBooking],
            ['preview' => $preview],
            ['cancellation' => $cancellation],
        ] as $parameters) {
            Livewire::actingAs($guest)
                ->test(GuestCancellationPage::class, $parameters)
                ->assertForbidden();
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0:User, 1:User, 2:SleepingPlace, 3:Booking}
     */
    private function createPaidBooking(array $overrides = []): array
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
                'display_name' => 'Quiet lower bed',
                'place_number' => 'L1',
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
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-12',
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-12',
            'check_in_time' => '15:00',
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
            'free_cancel_before' => '2026-07-09 15:00:00',
        ], $overrides));

        return [$guest, $host, $place, $booking];
    }
}
