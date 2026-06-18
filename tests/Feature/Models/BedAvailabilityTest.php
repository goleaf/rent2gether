<?php

namespace Tests\Feature\Models;

use App\Enums\BookingStatus;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private Bed $bed;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $room = Room::factory()->create(['property_id' => $property->id]);
        $this->bed = Bed::factory()->create(['room_id' => $room->id]);
    }

    public function test_bed_is_available_when_no_bookings_exist(): void
    {
        $available = Bed::availableForPeriod('2026-07-10', '2026-07-15')->where('id', $this->bed->id)->exists();

        $this->assertTrue($available);
    }

    public function test_bed_is_unavailable_when_booking_overlaps(): void
    {
        $guest = User::factory()->create();
        Booking::factory()->create([
            'bed_id' => $this->bed->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-07-12',
            'check_out' => '2026-07-18',
            'status' => BookingStatus::Confirmed,
        ]);

        $available = Bed::availableForPeriod('2026-07-10', '2026-07-15')->where('id', $this->bed->id)->exists();

        $this->assertFalse($available);
    }

    public function test_bed_is_available_when_booking_is_cancelled(): void
    {
        $guest = User::factory()->create();
        Booking::factory()->create([
            'bed_id' => $this->bed->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-07-12',
            'check_out' => '2026-07-18',
            'status' => BookingStatus::CancelledByGuest,
        ]);

        $available = Bed::availableForPeriod('2026-07-10', '2026-07-15')->where('id', $this->bed->id)->exists();

        $this->assertTrue($available);
    }

    public function test_bed_is_available_after_previous_booking_ends(): void
    {
        $guest = User::factory()->create();
        Booking::factory()->create([
            'bed_id' => $this->bed->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-15',
            'status' => BookingStatus::Confirmed,
        ]);

        $available = Bed::availableForPeriod('2026-07-15', '2026-07-20')->where('id', $this->bed->id)->exists();

        $this->assertTrue($available);
    }
}
