<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_true_when_bed_has_no_conflicts(): void
    {
        $bed = $this->createBed();

        $service = new AvailabilityService;

        $this->assertTrue($service->isAvailable($bed, '2026-07-10', '2026-07-15'));
    }

    public function test_it_returns_false_when_booking_overlaps(): void
    {
        $bed = $this->createBed();
        $guest = User::factory()->create();

        Booking::factory()->create([
            'bed_id' => $bed->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::Confirmed,
            'check_in' => '2026-07-12',
            'check_out' => '2026-07-18',
        ]);

        $service = new AvailabilityService;

        $this->assertFalse($service->isAvailable($bed, '2026-07-10', '2026-07-15'));
    }

    private function createBed(): Bed
    {
        $user = User::factory()->create();
        $property = Property::factory()->for($user, 'host')->create(['status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => 'active']);

        return Bed::factory()->for($room)->create(['status' => 'active']);
    }
}
