<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentRecordStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Booking\CancelBooking;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\CancellationService;
use App\Services\RefundCalculator;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CancellationRefundFlowTest extends TestCase
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

    public function test_flexible_early_cancellation_refunds_paid_amount(): void
    {
        [, , , $booking] = $this->createBooking([
            'cancellation_policy' => CancellationPolicy::Flexible,
            'free_cancel_before' => now()->addWeek(),
        ]);

        $estimate = app(RefundCalculator::class)->calculate($booking);

        $this->assertSame(175.0, $estimate->refundAmount);
        $this->assertSame(0.0, $estimate->nonRefundableAmount);
        $this->assertTrue($estimate->depositRefunded);
    }

    public function test_strict_late_cancellation_refunds_only_deposit_before_check_in(): void
    {
        Carbon::setTestNow('2026-07-09 12:00:00');
        CarbonImmutable::setTestNow('2026-07-09 12:00:00');

        [, , , $booking] = $this->createBooking([
            'cancellation_policy' => CancellationPolicy::Strict,
            'free_cancel_before' => now()->subWeek(),
            'check_in_date' => '2026-07-10',
            'check_in' => '2026-07-10',
            'check_in_time' => '15:00',
        ]);

        $estimate = app(RefundCalculator::class)->calculate($booking);

        $this->assertSame(50.0, $estimate->refundAmount);
        $this->assertSame(125.0, $estimate->nonRefundableAmount);
        $this->assertSame(50.0, $estimate->depositRefundAmount);
    }

    public function test_non_refundable_policy_still_refunds_deposit_before_check_in(): void
    {
        [, , , $booking] = $this->createBooking([
            'cancellation_policy' => CancellationPolicy::NonRefundable,
            'free_cancel_before' => now()->subWeek(),
        ]);

        $estimate = app(RefundCalculator::class)->calculate($booking);

        $this->assertSame(50.0, $estimate->refundAmount);
        $this->assertSame(125.0, $estimate->nonRefundableAmount);
    }

    public function test_host_cancellation_creates_full_refund_records(): void
    {
        [, $host, , $booking] = $this->createBooking();

        $cancelled = app(CancellationService::class)->cancelByHost($booking, 'maintenance', $host);

        $this->assertTrue($cancelled);
        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::CancelledByHost);
        $this->assertTrue($booking->payment_status === PaymentStatus::RefundedFull);
        $this->assertSame(175.0, (float) $booking->refund_amount);

        $this->assertDatabaseHas('refund_requests', [
            'booking_id' => $booking->id,
            'requested_by_user_id' => $host->id,
            'amount' => 175,
            'currency' => 'EUR',
        ]);
        $this->assertDatabaseHas('payment_records', [
            'booking_id' => $booking->id,
            'provider' => 'manual_refund_placeholder',
            'amount' => 175,
            'status' => PaymentRecordStatus::RefundedFull->value,
        ]);
    }

    public function test_guest_cancellation_releases_availability_and_notifies_both_sides(): void
    {
        [$guest, $host, $place, $booking] = $this->createBooking([
            'free_cancel_before' => now()->subWeek(),
        ]);

        foreach (['2026-07-10', '2026-07-11'] as $date) {
            AvailabilityDay::factory()->create([
                'sleeping_place_id' => $place->id,
                'booking_id' => $booking->id,
                'date' => $date,
                'status' => AvailabilityStatus::Booked,
            ]);
        }

        Livewire::actingAs($guest)
            ->test(CancelBooking::class, ['booking' => $booking])
            ->set('reason', 'plans_changed')
            ->set('details', 'My plans changed.')
            ->set('confirmed', true)
            ->call('submitCancellation')
            ->assertHasNoErrors();

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::CancelledByGuest);
        $this->assertSame('plans_changed', $booking->cancel_reason);

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10 00:00:00',
            'booking_id' => null,
            'status' => AvailabilityStatus::Available->value,
        ]);
        $this->assertDatabaseHas('booking_status_histories', [
            'booking_id' => $booking->id,
            'to_status' => BookingStatus::CancelledByGuest->value,
            'changed_by_user_id' => $guest->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'booking_cancelled_by_guest',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'booking_cancelled_by_guest',
        ]);
    }

    public function test_cancel_page_renders_localized_refund_lines(): void
    {
        [$guest, , , $booking] = $this->createBooking([
            'free_cancel_before' => now()->subWeek(),
        ]);

        $this->actingAs($guest)
            ->get(route('guest.bookings.cancel', ['locale' => 'ru', 'booking' => $booking]))
            ->assertOk()
            ->assertSeeLivewire(CancelBooking::class)
            ->assertSee(__('booking.cancellation.title', [], 'ru'))
            ->assertSee(__('booking.cancellation.lines.deposit', [], 'ru'))
            ->assertSee(__('booking.cancellation.reasons.plans_changed', [], 'ru'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0: User, 1: User, 2: SleepingPlace, 3: Booking}
     */
    private function createBooking(array $overrides = []): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->create(['is_host' => true]);
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
            ]);
        $place->translations()->createMany([
            [
                'locale' => 'en',
                'title' => 'Quiet lower bed',
                'summary' => 'A quiet bed.',
                'description' => 'A quiet bed.',
            ],
            [
                'locale' => 'ru',
                'title' => 'Тихое нижнее место',
                'summary' => 'Тихое место.',
                'description' => 'Тихое место.',
            ],
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
            'subtotal' => 100,
            'subtotal_amount' => 100,
            'discount_amount' => 0,
            'cleaning_fee' => 10,
            'cleaning_fee_amount' => 10,
            'deposit' => 50,
            'deposit_amount' => 50,
            'service_fee' => 15,
            'service_fee_amount' => 15,
            'total' => 175,
            'total_amount' => 175,
            'currency' => 'EUR',
            'cancellation_policy' => CancellationPolicy::Flexible,
            'free_cancel_before' => now()->addWeek(),
        ], $overrides));

        return [$guest, $host, $place, $booking];
    }
}
