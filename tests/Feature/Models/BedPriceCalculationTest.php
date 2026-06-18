<?php

namespace Tests\Feature\Models;

use App\Models\Bed;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedPriceCalculationTest extends TestCase
{
    use RefreshDatabase;

    private Bed $bed;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $room = Room::factory()->create(['property_id' => $property->id]);
        $this->bed = Bed::factory()->create([
            'room_id' => $room->id,
            'price_per_night' => 20.00,
            'cleaning_fee' => 10.00,
            'deposit' => 50.00,
            'discount_weekly' => 10,
            'discount_monthly' => 20,
        ]);
    }

    public function test_calculates_price_for_short_stay(): void
    {
        $result = $this->bed->calculatePrice('2026-07-10', '2026-07-13');

        $this->assertEquals(3, $result['nights']);
        $this->assertEquals(60.0, $result['subtotal']);
        $this->assertEquals(0.0, $result['discount']);
        $this->assertEquals(10.0, $result['cleaningFee']);
        $this->assertEquals(50.0, $result['deposit']);
    }

    public function test_applies_weekly_discount_for_7_nights(): void
    {
        $result = $this->bed->calculatePrice('2026-07-01', '2026-07-08');

        $this->assertEquals(7, $result['nights']);
        $this->assertEquals(140.0, $result['subtotal']);
        $this->assertEquals(14.0, $result['discount']); // 10%
    }

    public function test_applies_monthly_discount_for_30_nights(): void
    {
        $result = $this->bed->calculatePrice('2026-07-01', '2026-07-31');

        $this->assertEquals(30, $result['nights']);
        $subtotal = 30 * 20.0;
        $expectedDiscount = $subtotal * 0.20;
        $this->assertEquals($expectedDiscount, $result['discount']);
    }

    public function test_monthly_discount_takes_precedence_over_weekly(): void
    {
        $result = $this->bed->calculatePrice('2026-07-01', '2026-07-31');

        // monthly discount (20%) should apply, not weekly (10%)
        $this->assertEquals(30 * 20.0 * 0.20, $result['discount']);
    }

    public function test_total_includes_all_components(): void
    {
        $result = $this->bed->calculatePrice('2026-07-10', '2026-07-13');

        $expectedTotal = $result['subtotal'] - $result['discount'] + $result['cleaningFee'] + $result['deposit'] + $result['serviceFee'];
        $this->assertEquals($expectedTotal, $result['total']);
    }
}
