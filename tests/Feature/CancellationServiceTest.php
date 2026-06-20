<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\Bookings\CancellationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancellationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createBooking(array $overrides = []): Booking
    {
        $host = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $host->id]);
        $room = Room::factory()->for($property)->create();
        $bed = Bed::factory()->for($room)->create();

        return Booking::factory()->create(array_merge([
            'bed_id' => $bed->id,
            'guest_id' => User::factory()->create()->id,
            'host_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'status' => BookingStatus::Confirmed->value,
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
        ], $overrides));
    }

    public function test_free_cancellation_gives_full_refund(): void
    {
        $booking = $this->createBooking([
            'free_cancel_before' => now()->addDays(7),
            'cancellation_policy' => CancellationPolicy::Flexible->value,
        ]);

        $refund = app(CancellationService::class)->calculateRefund($booking);

        $this->assertSame(175.0, $refund['refund_amount']);
        $this->assertSame(0.0, (float) $refund['penalty_amount']);
    }

    public function test_flexible_policy_refunds_half_stay(): void
    {
        $booking = $this->createBooking([
            'free_cancel_before' => now()->subDay(),
            'cancellation_policy' => CancellationPolicy::Flexible->value,
        ]);

        $refund = app(CancellationService::class)->calculateRefund($booking);

        $this->assertSame(110.0, $refund['refund_amount']);
        $this->assertTrue($refund['deposit_refunded']);
    }

    public function test_non_refundable_refunds_deposit_before_check_in(): void
    {
        $booking = $this->createBooking([
            'free_cancel_before' => now()->subDay(),
            'cancellation_policy' => CancellationPolicy::NonRefundable->value,
        ]);

        $refund = app(CancellationService::class)->calculateRefund($booking);

        $this->assertSame(50.0, (float) $refund['refund_amount']);
        $this->assertTrue($refund['deposit_refunded']);
    }

    public function test_cancel_by_guest_updates_status(): void
    {
        $booking = $this->createBooking([
            'cancellation_policy' => CancellationPolicy::Flexible->value,
            'free_cancel_before' => now()->addDay(),
        ]);

        $ok = app(CancellationService::class)->cancelByGuest($booking, 'Plans changed');

        $this->assertTrue($ok);
        $this->assertSame(BookingStatus::CancelledByGuest, $booking->fresh()->status);
    }

    public function test_cancel_by_host_gives_full_refund(): void
    {
        $booking = $this->createBooking();

        $ok = app(CancellationService::class)->cancelByHost($booking, 'Maintenance');

        $this->assertTrue($ok);
        $fresh = $booking->fresh();
        $this->assertSame(BookingStatus::CancelledByHost, $fresh->status);
        $this->assertSame(175.0, (float) $fresh->refund_amount);
    }
}
