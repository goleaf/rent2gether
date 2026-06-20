<?php

namespace Tests\Unit;

use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\AvailabilityDay;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Pricing\PricingService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-19 12:00:00');
        CarbonImmutable::setTestNow('2026-06-19 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_july_ten_to_fifteen_calculates_five_nights_and_six_calendar_days(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 20,
            'cleaning_fee' => 0,
            'deposit_amount' => 0,
        ]);

        $quote = (new PricingService)->calculate($guest, $place, '2026-07-10', '2026-07-15');

        $this->assertSame(5, $quote->nightsCount);
        $this->assertSame(6, $quote->calendarDaysCount);
        $this->assertSame(3, $quote->weekdayCount);
        $this->assertSame(2, $quote->weekendCount);
        $this->assertSame('Friday', $quote->checkInWeekday);
        $this->assertSame('Wednesday', $quote->checkOutWeekday);
        $this->assertSame(100.0, $quote->baseAmount);
        $this->assertSame(100.0, $quote->subtotalAmount);
        $this->assertSame(5.0, $quote->serviceFeeAmount);
        $this->assertSame(105.0, $quote->totalAmount);
    }

    public function test_weekly_discount_is_applied_after_seven_nights(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 100,
            'weekly_price' => 600,
            'cleaning_fee' => 0,
            'deposit_amount' => 0,
        ]);

        $quote = (new PricingService)->calculate($guest, $place, '2026-07-10', '2026-07-17');

        $this->assertSame(7, $quote->nightsCount);
        $this->assertSame(100.0, $quote->weeklyDiscountAmount);
        $this->assertSame(0.0, $quote->monthlyDiscountAmount);
        $this->assertSame(600.0, $quote->subtotalAmount);
        $this->assertSame(630.0, $quote->totalAmount);
        $this->assertLineAmount($quote->lineItems, 'weekly_discount', -100.0);
    }

    public function test_monthly_discount_takes_priority_for_thirty_nights(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 100,
            'weekly_price' => 600,
            'monthly_price' => 2500,
            'cleaning_fee' => 0,
            'deposit_amount' => 0,
        ]);

        $quote = (new PricingService)->calculate($guest, $place, '2026-07-01', '2026-07-31');

        $this->assertSame(30, $quote->nightsCount);
        $this->assertSame(0.0, $quote->weeklyDiscountAmount);
        $this->assertSame(500.0, $quote->monthlyDiscountAmount);
        $this->assertSame(2500.0, $quote->subtotalAmount);
        $this->assertSame(2625.0, $quote->totalAmount);
        $this->assertLineAmount($quote->lineItems, 'monthly_discount', -500.0);
    }

    public function test_weekend_price_adjusts_weekend_nights(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 100,
            'weekend_price' => 150,
            'cleaning_fee' => 0,
            'deposit_amount' => 0,
        ]);

        $quote = (new PricingService)->calculate($guest, $place, '2026-07-10', '2026-07-13');

        $this->assertSame(3, $quote->nightsCount);
        $this->assertSame(2, $quote->weekendCount);
        $this->assertSame(100.0, $quote->weekendAdjustmentAmount);
        $this->assertSame(400.0, $quote->subtotalAmount);
        $this->assertSame(420.0, $quote->totalAmount);
        $this->assertLineAmount($quote->lineItems, 'weekend_adjustment', 100.0);
    }

    public function test_date_override_replaces_that_night_price(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 100,
            'cleaning_fee' => 0,
            'deposit_amount' => 0,
        ]);
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-11',
            'price_override' => 180,
        ]);

        $quote = (new PricingService)->calculate($guest, $place, '2026-07-10', '2026-07-12');

        $this->assertSame(80.0, $quote->dateOverrideAmount);
        $this->assertSame(280.0, $quote->subtotalAmount);
        $this->assertSame('date_override', $quote->datePrices[1]['source']);
        $this->assertSame(180.0, $quote->datePrices[1]['price']);
        $this->assertLineAmount($quote->lineItems, 'date_override', 80.0);
    }

    public function test_deposit_and_cleaning_fee_are_reported_separately(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 100,
            'cleaning_fee' => 25,
            'deposit_amount' => 80,
        ]);

        $quote = (new PricingService)->calculate($guest, $place, '2026-07-10', '2026-07-12');

        $this->assertSame(25.0, $quote->cleaningFeeAmount);
        $this->assertSame(80.0, $quote->depositAmount);
        $this->assertSame(80.0, $quote->refundableAmount);
        $this->assertSame(235.0, $quote->nonRefundableAmount);
        $this->assertSame(315.0, $quote->totalAmount);
        $this->assertLineAmount($quote->lineItems, 'cleaning_fee', 25.0);
        $this->assertLineAmount($quote->lineItems, 'deposit', 80.0);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{0: User, 1: SleepingPlace}
     */
    private function sleepingPlace(array $attributes = []): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->create(['is_host' => true]);
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
            ->create(array_merge([
                'status' => SleepingPlaceStatus::Active,
                'base_price_per_night' => 100,
                'weekly_price' => null,
                'monthly_price' => null,
                'weekend_price' => null,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => null,
            ], $attributes));

        return [$guest, $place];
    }

    /**
     * @param  list<array{type:string,amount:float}>  $lines
     */
    private function assertLineAmount(array $lines, string $type, float $amount): void
    {
        $line = collect($lines)->firstWhere('type', $type);

        $this->assertNotNull($line);
        $this->assertSame($amount, $line['amount']);
    }
}
