<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Bed;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\Bookings\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createBed(array $overrides = []): Bed
    {
        $host = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $host->id]);
        $room = Room::factory()->for($property)->create();

        return Bed::factory()->for($room)->create(array_merge([
            'price_per_night' => 25,
            'min_nights' => 1,
            'max_nights' => 90,
            'max_guests' => 2,
            'instant_book' => true,
        ], $overrides));
    }

    public function test_create_booking_succeeds(): void
    {
        $bed = $this->createBed();
        $guest = User::factory()->create();

        $result = app(BookingService::class)->create($guest, $bed, '2026-08-01', '2026-08-04');

        $this->assertTrue($result['success']);
        $this->assertSame(3, (int) $result['booking']->nights);
        $this->assertSame($guest->id, $result['booking']->guest_id);
    }

    public function test_create_booking_fails_checkout_before_checkin(): void
    {
        $bed = $this->createBed();
        $guest = User::factory()->create();

        $result = app(BookingService::class)->create($guest, $bed, '2026-08-05', '2026-08-01');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('after', $result['error']);
    }

    public function test_create_booking_fails_below_min_nights(): void
    {
        $bed = $this->createBed(['min_nights' => 3]);
        $guest = User::factory()->create();

        $result = app(BookingService::class)->create($guest, $bed, '2026-08-01', '2026-08-02');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Minimum', $result['error']);
    }

    public function test_create_booking_fails_above_max_nights(): void
    {
        $bed = $this->createBed(['max_nights' => 5]);
        $guest = User::factory()->create();

        $result = app(BookingService::class)->create($guest, $bed, '2026-08-01', '2026-08-20');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Maximum', $result['error']);
    }

    public function test_create_booking_fails_too_many_guests(): void
    {
        $bed = $this->createBed(['max_guests' => 1]);
        $guest = User::factory()->create();

        $result = app(BookingService::class)->create($guest, $bed, '2026-08-01', '2026-08-04', 3);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('maximum', $result['error']);
    }

    public function test_instant_book_sets_pending_payment_status(): void
    {
        $bed = $this->createBed(['instant_book' => true]);
        $guest = User::factory()->create();

        $result = app(BookingService::class)->create($guest, $bed, '2026-08-01', '2026-08-04');

        $this->assertSame(BookingStatus::PendingPayment, $result['booking']->status);
    }

    public function test_non_instant_sets_pending_host_status(): void
    {
        $bed = $this->createBed(['instant_book' => false]);
        $guest = User::factory()->create();

        $result = app(BookingService::class)->create($guest, $bed, '2026-08-01', '2026-08-04');

        $this->assertSame(BookingStatus::PendingHostConfirmation, $result['booking']->status);
    }

    public function test_confirm_by_host(): void
    {
        $bed = $this->createBed(['instant_book' => false]);
        $guest = User::factory()->create();
        $result = app(BookingService::class)->create($guest, $bed, '2026-08-01', '2026-08-04');

        $ok = app(BookingService::class)->confirmByHost($result['booking']);

        $this->assertTrue($ok);
        $this->assertSame(BookingStatus::PendingPayment, $result['booking']->fresh()->status);
    }

    public function test_reject_by_host(): void
    {
        $bed = $this->createBed(['instant_book' => false]);
        $guest = User::factory()->create();
        $result = app(BookingService::class)->create($guest, $bed, '2026-08-01', '2026-08-04');

        $ok = app(BookingService::class)->rejectByHost($result['booking'], 'Not a good fit');

        $this->assertTrue($ok);
        $this->assertSame(BookingStatus::CancelledByHost, $result['booking']->fresh()->status);
    }

    public function test_full_lifecycle_checkin_checkout_complete(): void
    {
        $bed = $this->createBed();
        $guest = User::factory()->create();
        $service = app(BookingService::class);

        $result = $service->create($guest, $bed, '2026-08-01', '2026-08-04');
        $booking = $result['booking'];

        $service->markPaid($booking);
        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);

        $service->checkIn($booking);
        $this->assertSame(BookingStatus::CheckedIn, $booking->fresh()->status);

        $service->checkOut($booking);
        $this->assertSame(BookingStatus::CheckedOut, $booking->fresh()->status);

        $service->complete($booking);
        $this->assertSame(BookingStatus::Completed, $booking->fresh()->status);
    }
}
