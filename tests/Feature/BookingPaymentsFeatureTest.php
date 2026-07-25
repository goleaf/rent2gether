<?php

namespace Tests\Feature;

use App\Livewire\Bookings\Payments\BookingPaymentPage;
use App\Livewire\Bookings\Payments\PaymentAttemptsList;
use App\Livewire\Bookings\Payments\PaymentBreakdown;
use App\Livewire\Bookings\Payments\PaymentDeadlineBanner;
use App\Livewire\Bookings\Payments\PaymentMethodPicker;
use App\Livewire\Bookings\Payments\PaymentReceiptCard;
use App\Livewire\Bookings\Payments\PaymentStatusBadge;
use App\Livewire\Bookings\Payments\PaymentSummaryCard;
use App\Livewire\Bookings\Payments\RefundStatusCard;
use App\Livewire\Host\Payments\HostBookingPaymentStatus;
use App\Livewire\Host\Payments\HostPaymentSummaryCard;
use App\Livewire\Host\Payments\HostRefundStatusCard;
use App\Models\Booking;
use App\Models\BookingRefund;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\User;
use App\Services\Bookings\BookingPaymentAttemptService;
use App\Services\Bookings\BookingPaymentCreationService;
use App\Services\Bookings\BookingPaymentExpirationService;
use App\Services\Bookings\BookingPaymentNumberService;
use App\Services\Bookings\BookingPaymentPrivacyService;
use App\Services\Bookings\BookingPaymentService;
use App\Services\Bookings\BookingReceiptService;
use App\Services\Bookings\BookingRefundService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingPaymentsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow('2027-01-10 10:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_payment_numbers_are_generated_for_payments_refunds_and_receipts(): void
    {
        $numbers = app(BookingPaymentNumberService::class);

        $this->assertSame('PAY-2027-000001', $numbers->generatePaymentNumber());
        $this->assertSame('REF-2027-000001', $numbers->generateRefundNumber());
        $this->assertSame('RCT-2027-000001', $numbers->generateReceiptNumber());
    }

    public function test_payment_can_be_created_for_booking_with_context_allocations_and_deadline(): void
    {
        [$guest, $host, $place, $booking] = $this->createPayableBooking();

        $payment = app(BookingPaymentCreationService::class)->createForBooking($booking);

        $this->assertSame($booking->id, $payment->booking_id);
        $this->assertSame($guest->id, $payment->guest_user_id);
        $this->assertSame($host->id, $payment->host_user_id);
        $this->assertSame($place->id, $payment->sleeping_place_id);
        $this->assertSame('waiting_payment', $payment->status);
        $this->assertSame(126.0, (float) $payment->amount);
        $this->assertSame('EUR', $payment->currency);
        $this->assertNotNull($payment->payment_deadline_at);

        $this->assertDatabaseHas('booking_payment_allocations', [
            'booking_payment_id' => $payment->id,
            'allocation_type' => 'accommodation',
            'amount' => 60,
            'refundable' => false,
        ]);
        $this->assertDatabaseHas('booking_payment_allocations', [
            'booking_payment_id' => $payment->id,
            'allocation_type' => 'cleaning_fee',
            'amount' => 10,
            'refundable' => false,
        ]);
        $this->assertDatabaseHas('booking_payment_allocations', [
            'booking_payment_id' => $payment->id,
            'allocation_type' => 'guest_service_fee',
            'amount' => 6,
            'refundable' => false,
        ]);
        $this->assertDatabaseHas('booking_payment_allocations', [
            'booking_payment_id' => $payment->id,
            'allocation_type' => 'deposit',
            'amount' => 50,
            'refundable' => true,
        ]);
        $this->assertDatabaseHas('booking_payment_deadlines', [
            'booking_payment_id' => $payment->id,
            'booking_id' => $booking->id,
            'deadline_type' => 'initial_payment',
            'status' => 'pending',
        ]);
    }

    public function test_payment_attempt_can_fail_without_releasing_locks_before_deadline_and_then_succeed(): void
    {
        [, , , $booking] = $this->createPayableBooking();
        $this->createPaymentPendingLocks($booking);
        $payment = app(BookingPaymentCreationService::class)->createForBooking($booking);

        $attempts = app(BookingPaymentAttemptService::class);
        $failedAttempt = $attempts->startAttempt($booking->guest, $payment, 'internal_test');
        $attempts->markAttemptFailed($failedAttempt, 'declined');

        $payment->refresh();
        $booking->refresh();

        $this->assertSame('waiting_payment', $payment->status);
        $this->assertSame('waiting_payment', $booking->payment_status->value);
        $this->assertSame(3, $booking->sleepingPlaceDateLocks()->where('status', 'active')->count());

        $successfulAttempt = $attempts->startAttempt($booking->guest, $payment, 'internal_test');
        $attempts->markAttemptSucceeded($successfulAttempt);

        $payment->refresh();
        $booking->refresh();

        $this->assertSame('paid', $payment->status);
        $this->assertSame('paid', $booking->payment_status->value);
        $this->assertTrue(app(BookingPaymentService::class)->isFullyPaid($booking));
        $this->assertSame(3, $booking->sleepingPlaceDateLocks()->where('lock_type', 'booked')->where('status', 'active')->count());
    }

    public function test_payment_expiration_releases_payment_locks_without_cron(): void
    {
        [, , , $booking] = $this->createPayableBooking([
            'payment_deadline_at' => now()->addMinutes(15),
        ]);
        $this->createPaymentPendingLocks($booking);
        $payment = app(BookingPaymentCreationService::class)->createForBooking($booking);

        CarbonImmutable::setTestNow('2027-01-10 10:20:00');

        app(BookingPaymentExpirationService::class)->expirePayment($payment);

        $payment->refresh();
        $booking->refresh();

        $this->assertSame('expired', $payment->status);
        $this->assertSame('failed', $booking->payment_status->value);
        $this->assertSame('payment_failed', $booking->status->value);
        $this->assertSame(0, $booking->sleepingPlaceDateLocks()->where('status', 'active')->count());
    }

    public function test_partial_payment_and_remaining_balance_are_supported(): void
    {
        [, , , $booking] = $this->createPayableBooking([
            'payment_type' => 'partial_payment',
        ]);

        $payment = app(BookingPaymentCreationService::class)->createForBooking($booking, [
            'required_now_amount' => 90,
            'remaining_amount' => 36,
            'remaining_due_at' => now()->addDays(10),
        ]);

        app(BookingPaymentAttemptService::class)
            ->markAttemptSucceeded(app(BookingPaymentAttemptService::class)->startAttempt($booking->guest, $payment, 'internal_test'), [
                'amount' => 90,
            ]);

        $payment->refresh();
        $booking->refresh();

        $this->assertSame('partially_paid', $payment->status);
        $this->assertSame('partially_paid', $booking->payment_status->value);
        $this->assertSame(36.0, (float) $payment->remaining_amount);

        $remaining = app(BookingPaymentCreationService::class)->createRemainingBalancePayment($booking);

        $this->assertSame('remaining_balance', $remaining->payment_type);
        $this->assertSame(36.0, (float) $remaining->amount);
    }

    public function test_receipts_and_basic_refunds_can_be_created_and_completed(): void
    {
        [, , , $booking] = $this->createPayableBooking();
        $payment = app(BookingPaymentCreationService::class)->createForBooking($booking);
        app(BookingPaymentAttemptService::class)
            ->markAttemptSucceeded(app(BookingPaymentAttemptService::class)->startAttempt($booking->guest, $payment, 'internal_test'));

        $receipt = app(BookingReceiptService::class)->createReceipt($payment->fresh());
        $refunds = app(BookingRefundService::class);
        $fullRefund = $refunds->createFullRefund($booking->fresh(), 'guest_cancelled');
        $partialRefund = $refunds->createPartialRefund($booking->fresh(), 20, 'partial_adjustment');
        $depositRefund = $refunds->createDepositRefund($booking->fresh(), 50);
        $refunds->markRefundCompleted($depositRefund);

        $this->assertStringStartsWith('RCT-2027-', $receipt->receipt_number);
        $this->assertStringStartsWith('REF-2027-', $fullRefund->refund_number);
        $this->assertSame('full_refund', $fullRefund->refund_type);
        $this->assertSame('partial_refund', $partialRefund->refund_type);
        $this->assertSame('deposit_refund', $depositRefund->refresh()->refund_type);
        $this->assertSame('completed', $depositRefund->status);
    }

    public function test_payment_privacy_allows_owners_and_hides_provider_payload_from_host(): void
    {
        [$guest, $host, , $booking] = $this->createPayableBooking();
        $otherGuest = User::factory()->create();
        $otherHost = User::factory()->host()->create();
        $payment = app(BookingPaymentCreationService::class)->createForBooking($booking);
        $payment->forceFill([
            'provider' => 'future_provider',
            'provider_payment_id' => 'pi_secret',
            'provider_payload_json' => ['private' => 'payload'],
        ])->save();

        $privacy = app(BookingPaymentPrivacyService::class);

        $this->assertTrue($privacy->canGuestViewPayment($guest, $payment));
        $this->assertFalse($privacy->canGuestViewPayment($otherGuest, $payment));
        $this->assertTrue($privacy->canHostViewPayment($host, $payment));
        $this->assertFalse($privacy->canHostViewPayment($otherHost, $payment));

        $hostView = $privacy->filterPaymentForHost($host, $payment->fresh());

        $this->assertArrayNotHasKey('provider_payload_json', $hostView);
        $this->assertArrayNotHasKey('provider_payment_id', $hostView);
    }

    public function test_payment_components_render_in_english_and_russian(): void
    {
        [$guest, $host, , $booking] = $this->createPayableBooking();
        $payment = app(BookingPaymentCreationService::class)->createForBooking($booking);
        $refund = BookingRefund::factory()->for($booking)->create([
            'booking_payment_id' => $payment->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'refund_type' => 'deposit_refund',
            'status' => 'pending',
            'amount' => 50,
            'currency' => 'EUR',
        ]);

        foreach (['en', 'ru'] as $locale) {
            app()->setLocale($locale);

            Livewire::actingAs($guest)
                ->test(PaymentSummaryCard::class, ['paymentId' => $payment->id])
                ->assertSee(__('payments.title', [], $locale))
                ->assertSee(__('payments.fields.amount', [], $locale));

            Livewire::actingAs($guest)
                ->test(PaymentDeadlineBanner::class, ['paymentId' => $payment->id])
                ->assertSee(__('payments.fields.payment_deadline', [], $locale));

            Livewire::actingAs($guest)
                ->test(PaymentBreakdown::class, ['paymentId' => $payment->id])
                ->assertSee(__('payments.allocation_types.deposit', [], $locale));

            Livewire::actingAs($guest)
                ->test(PaymentAttemptsList::class, ['paymentId' => $payment->id])
                ->assertSee(__('payments.empty_states.no_attempts', [], $locale));

            Livewire::actingAs($guest)
                ->test(PaymentReceiptCard::class, ['paymentId' => $payment->id])
                ->assertSee(__('payments.empty_states.no_receipt', [], $locale));

            Livewire::actingAs($guest)
                ->test(PaymentStatusBadge::class, ['paymentId' => $payment->id])
                ->assertSee(__('payments.statuses.waiting_payment', [], $locale));

            Livewire::actingAs($guest)
                ->test(PaymentMethodPicker::class, ['paymentId' => $payment->id])
                ->set('paymentMethod', 'manual_confirmation_future')
                ->call('save')
                ->assertHasNoErrors();

            Livewire::actingAs($guest)
                ->test(RefundStatusCard::class, ['refundId' => $refund->id])
                ->assertSee(__('payments.refund_types.deposit_refund', [], $locale));

            Livewire::actingAs($host)
                ->test(HostPaymentSummaryCard::class, ['paymentId' => $payment->id])
                ->assertSee(__('payments.host.summary_title', [], $locale));

            Livewire::actingAs($host)
                ->test(HostBookingPaymentStatus::class, ['paymentId' => $payment->id])
                ->assertSee(__('payments.host.status_title', [], $locale));

            Livewire::actingAs($host)
                ->test(HostRefundStatusCard::class, ['refundId' => $refund->id])
                ->assertSee(__('payments.host.refund_title', [], $locale));
        }
    }

    public function test_payment_components_forbid_non_owner_users(): void
    {
        [, , , $booking] = $this->createPayableBooking();
        $payment = app(BookingPaymentCreationService::class)->createForBooking($booking);
        $refund = BookingRefund::factory()->for($booking)->create([
            'booking_payment_id' => $payment->id,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'refund_type' => 'deposit_refund',
            'status' => 'pending',
            'amount' => 50,
            'currency' => 'EUR',
        ]);
        $otherGuest = User::factory()->create();
        $otherHost = User::factory()->host()->create();

        $this->actingAs($otherGuest)
            ->get(route('guest.bookings.payment', ['locale' => 'en', 'booking' => $booking]))
            ->assertForbidden();

        Livewire::actingAs($otherGuest)
            ->test(BookingPaymentPage::class, ['paymentId' => $payment->id])
            ->assertForbidden();

        Livewire::actingAs($otherGuest)
            ->test(PaymentSummaryCard::class, ['paymentId' => $payment->id])
            ->assertForbidden();

        Livewire::actingAs($otherGuest)
            ->test(PaymentMethodPicker::class, ['paymentId' => $payment->id])
            ->assertForbidden();

        Livewire::actingAs($otherGuest)
            ->test(RefundStatusCard::class, ['refundId' => $refund->id])
            ->assertForbidden();

        Livewire::actingAs($otherHost)
            ->test(HostPaymentSummaryCard::class, ['paymentId' => $payment->id])
            ->assertForbidden();

        Livewire::actingAs($otherHost)
            ->test(HostRefundStatusCard::class, ['refundId' => $refund->id])
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0: User, 1: User, 2: SleepingPlace, 3: Booking}
     */
    private function createPayableBooking(array $overrides = []): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $property = Property::factory()->create(['host_user_id' => $host->id]);
        $room = Room::factory()->for($property)->create(['user_id' => $host->id]);
        $place = SleepingPlace::factory()->for($property)->for($room)->create([
            'user_id' => $host->id,
            'max_guests' => 1,
            'max_guests_count' => 1,
            'base_price' => 20,
            'base_price_per_night' => 20,
            'cleaning_fee' => 10,
            'deposit_amount' => 50,
            'currency' => 'EUR',
        ]);

        $booking = Booking::factory()->create([
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => '2027-02-10',
            'check_out_date' => '2027-02-13',
            'check_in' => '2027-02-10',
            'check_out' => '2027-02-13',
            'nights_count' => 3,
            'chargeable_days_count' => 3,
            'calendar_presence_days_count' => 4,
            'guests_count' => 1,
            'accommodation_amount' => 60,
            'cleaning_fee_amount' => 10,
            'service_fee_amount' => 6,
            'deposit_amount' => 50,
            'total_without_deposit' => 76,
            'total_payable' => 126,
            'total_amount' => 126,
            'host_payout_amount' => 60,
            'refundable_amount' => 50,
            'non_refundable_amount' => 76,
            'currency' => 'EUR',
            'status' => 'waiting_payment',
            'payment_status' => 'waiting_payment',
            'payment_method' => 'internal_test',
            'payment_deadline_at' => now()->addMinutes(30),
            ...$overrides,
        ]);

        return [$guest, $host, $place, $booking];
    }

    private function createPaymentPendingLocks(Booking $booking): void
    {
        foreach (['2027-02-10', '2027-02-11', '2027-02-12'] as $date) {
            SleepingPlaceBookingDateLock::factory()
                ->for($booking->sleepingPlace)
                ->paymentPending()
                ->create([
                    'booking_id' => $booking->id,
                    'date' => $date,
                ]);
        }
    }
}
