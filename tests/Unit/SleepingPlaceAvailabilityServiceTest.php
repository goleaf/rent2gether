<?php

namespace Tests\Unit;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SleepingPlaceAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_booking_overlap_blocks_sleeping_place(): void
    {
        [$place, $guest, $host] = $this->sleepingPlace();

        Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create([
                'check_in_date' => '2026-07-12',
                'check_out_date' => '2026-07-15',
                'status' => BookingStatus::Confirmed,
            ]);

        $service = new AvailabilityService;

        $this->assertFalse($service->isAvailable($place, '2026-07-10', '2026-07-13'));
        $this->assertSame(['2026-07-12'], $service->unavailableDates($place, '2026-07-10', '2026-07-13'));
    }

    public function test_checkout_same_day_as_next_checkin_is_allowed_when_boundary_rules_allow_it(): void
    {
        [$place, $guest, $host] = $this->sleepingPlace();

        Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create([
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-12',
                'status' => BookingStatus::Confirmed,
            ]);

        $this->assertTrue((new AvailabilityService)->isAvailable($place, '2026-07-12', '2026-07-14'));
    }

    public function test_property_room_and_sleeping_place_statuses_block_availability(): void
    {
        [$place] = $this->sleepingPlace();
        $service = new AvailabilityService;

        $place->property->update(['status' => PropertyStatus::Hidden]);
        $this->assertFalse($service->isAvailable($place->refresh(), '2026-07-10', '2026-07-12'));

        $place->property->update(['status' => PropertyStatus::Active]);
        $place->room->update(['status' => RoomStatus::Unavailable]);
        $this->assertFalse($service->isAvailable($place->refresh(), '2026-07-10', '2026-07-12'));

        $place->room->update(['status' => RoomStatus::Active]);
        $place->update(['status' => SleepingPlaceStatus::Repair]);
        $this->assertFalse($service->isAvailable($place->refresh(), '2026-07-10', '2026-07-12'));
    }

    public function test_pending_payment_hold_blocks_and_can_be_released(): void
    {
        [$place, $guest, $host] = $this->sleepingPlace();
        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create([
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-12',
                'status' => BookingStatus::PendingPayment,
            ]);
        $service = new AvailabilityService;

        $service->blockForBooking($booking);

        $this->assertFalse($service->isAvailable($place, '2026-07-10', '2026-07-12'));
        $this->assertDatabaseHas('availability_days', [
            'booking_id' => $booking->id,
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10 00:00:00',
            'status' => AvailabilityStatus::PendingPayment->value,
        ]);

        $booking->update(['status' => BookingStatus::CancelledByGuest]);
        $service->releaseForBooking($booking->refresh());

        $this->assertTrue($service->isAvailable($place, '2026-07-10', '2026-07-12'));
        $this->assertDatabaseHas('availability_days', [
            'booking_id' => null,
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10 00:00:00',
            'status' => AvailabilityStatus::Available->value,
        ]);
    }

    public function test_host_blocked_dates_and_repair_dates_are_unavailable(): void
    {
        [$place] = $this->sleepingPlace();
        $service = new AvailabilityService;

        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-13',
            'status' => AvailabilityStatus::Repair,
        ]);

        $this->assertFalse($service->isAvailable($place, '2026-07-10', '2026-07-12'));
        $this->assertFalse($service->isAvailable($place, '2026-07-13', '2026-07-14'));
        $this->assertSame(['2026-07-11', '2026-07-13'], $service->unavailableDates($place, '2026-07-10', '2026-07-15'));
    }

    public function test_nearest_available_ranges_skip_blocked_dates(): void
    {
        [$place] = $this->sleepingPlace();

        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-10',
            'status' => AvailabilityStatus::Repair,
        ]);
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);

        $ranges = (new AvailabilityService)->nearestAvailableRanges($place, '2026-07-10', 2);

        $this->assertSame([
            ['check_in' => '2026-07-12', 'check_out' => '2026-07-14', 'nights' => 2],
            ['check_in' => '2026-07-14', 'check_out' => '2026-07-16', 'nights' => 2],
            ['check_in' => '2026-07-16', 'check_out' => '2026-07-18', 'nights' => 2],
        ], $ranges);
    }

    public function test_check_in_and_check_out_only_rules_are_respected(): void
    {
        [$place] = $this->sleepingPlace();
        $service = new AvailabilityService;

        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-10',
            'status' => AvailabilityStatus::CheckOutOnly,
        ]);
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-12',
            'status' => AvailabilityStatus::CheckInOnly,
        ]);

        $this->assertFalse($service->isAvailable($place, '2026-07-10', '2026-07-12'));
        $this->assertFalse($service->isAvailable($place, '2026-07-11', '2026-07-12'));
        $this->assertTrue($service->isAvailable($place, '2026-07-12', '2026-07-14'));
    }

    /**
     * @return array{0: SleepingPlace, 1: User, 2: User}
     */
    private function sleepingPlace(): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $guest = User::factory()->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'status' => PropertyStatus::Active,
            ]);
        $room = Room::factory()->for($property)->create(['status' => RoomStatus::Active]);
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create(['status' => SleepingPlaceStatus::Active]);

        return [$place, $guest, $host];
    }
}
