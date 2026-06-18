<?php

namespace Tests\Unit;

use App\Models\Bed;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingPriceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPriceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_nights_and_total_for_short_stay(): void
    {
        $bed = $this->createBed([
            'price_per_night' => 20,
            'cleaning_fee' => 10,
            'deposit' => 50,
        ]);

        $result = (new BookingPriceCalculator)->calculate($bed, '2026-07-10', '2026-07-13');

        $this->assertSame(3, (int) $result['nights']);
        $this->assertSame(4, (int) $result['calendar_days']);
        $this->assertSame(60.0, $result['subtotal']);
        $this->assertSame(10.0, $result['cleaning_fee']);
        $this->assertSame(50.0, $result['deposit']);
        $this->assertSame(3.0, $result['service_fee']);
        $this->assertSame(123.0, $result['total']);
    }

    private function createBed(array $attributes = []): Bed
    {
        $user = User::factory()->create();
        $property = Property::factory()->for($user, 'host')->create(['status' => 'active']);
        $room = Room::factory()->for($property)->create(['status' => 'active']);

        return Bed::factory()->for($room)->create(array_merge([
            'status' => 'active',
            'price_per_night' => 15,
        ], $attributes));
    }
}
