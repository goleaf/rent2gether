<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RefundRequestStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Host\HostIncome;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Number;
use Livewire\Livewire;
use Tests\TestCase;

class HostIncomeScreenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-15 10:00:00');
        CarbonImmutable::setTestNow('2026-07-15 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_paid_booking_is_counted_as_confirmed_income(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        $this->booking($host, $place, [
            'total_amount' => 120,
            'total' => 120,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Confirmed,
        ]);

        Livewire::actingAs($host)
            ->test(HostIncome::class)
            ->assertSet('summary.confirmed_income', 120.0)
            ->assertSet('summary.confirmed_count', 1)
            ->assertSee(Number::currency(120, 'EUR', 'en'));
    }

    public function test_unpaid_booking_is_not_counted_as_confirmed_income(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        $this->booking($host, $place, [
            'total_amount' => 200,
            'total' => 200,
            'payment_status' => PaymentStatus::AwaitingPayment,
            'status' => BookingStatus::AwaitingPayment,
        ]);

        Livewire::actingAs($host)
            ->test(HostIncome::class)
            ->assertSet('summary.confirmed_income', 0.0)
            ->assertSet('summary.pending_payments_amount', 200.0)
            ->assertSet('summary.pending_payments_count', 1);
    }

    public function test_refund_reduces_income(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        $booking = $this->booking($host, $place, [
            'total_amount' => 150,
            'total' => 150,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Completed,
        ]);
        $booking->refundRequests()->create([
            'requested_by_user_id' => $booking->guest_user_id,
            'amount' => 45,
            'currency' => 'EUR',
            'reason' => 'plans_changed',
            'status' => RefundRequestStatus::Paid,
        ]);

        Livewire::actingAs($host)
            ->test(HostIncome::class)
            ->assertSet('summary.confirmed_gross', 150.0)
            ->assertSet('summary.refunds_amount', 45.0)
            ->assertSet('summary.confirmed_income', 105.0);
    }

    public function test_host_sees_own_income_only(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        [, $otherPlace] = $this->hostSleepingPlace();
        $this->booking($host, $place, [
            'total_amount' => 80,
            'total' => 80,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Confirmed,
        ]);
        $this->booking($otherPlace->property->host, $otherPlace, [
            'total_amount' => 500,
            'total' => 500,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Confirmed,
        ]);

        Livewire::actingAs($host)
            ->test(HostIncome::class)
            ->assertSet('summary.confirmed_income', 80.0)
            ->assertDontSee(Number::currency(500, 'EUR', 'en'));
    }

    public function test_income_page_uses_localized_currency_display(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        $this->booking($host, $place, [
            'total_amount' => 1234.56,
            'total' => 1234.56,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Confirmed,
        ]);

        $this->actingAs($host)
            ->get(route('host.income', ['locale' => 'ru']))
            ->assertOk()
            ->assertSeeLivewire(HostIncome::class)
            ->assertSee(Number::currency(1234.56, 'EUR', 'ru'), false);
    }

    public function test_custom_range_filter_limits_income(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        $this->booking($host, $place, [
            'check_in_date' => '2026-07-10',
            'check_in' => '2026-07-10',
            'total_amount' => 90,
            'total' => 90,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Confirmed,
        ]);
        $this->booking($host, $place, [
            'check_in_date' => '2026-08-10',
            'check_in' => '2026-08-10',
            'total_amount' => 140,
            'total' => 140,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Confirmed,
        ]);

        Livewire::actingAs($host)
            ->test(HostIncome::class)
            ->set('datePreset', 'custom')
            ->set('customStart', '2026-08-01')
            ->set('customEnd', '2026-08-31')
            ->call('applyFilters')
            ->assertHasNoErrors()
            ->assertSet('summary.confirmed_income', 140.0);
    }

    /**
     * @return array{0: User, 1: SleepingPlace}
     */
    private function hostSleepingPlace(): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'status' => PropertyStatus::Active,
                'title' => 'Income property',
            ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Income room',
        ]);
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'display_name' => 'Income sleeping place',
                'place_number' => 'I1',
                'currency' => 'EUR',
            ]);

        return [$host, $place];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function booking(User $host, SleepingPlace $place, array $overrides = []): Booking
    {
        return Booking::factory()
            ->for(User::factory()->create(['name' => 'Income Guest']), 'guest')
            ->for($host, 'host')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create(array_merge([
                'host_user_id' => $host->id,
                'host_id' => $host->id,
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
                'sleeping_place_id' => $place->id,
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-12',
                'check_in' => '2026-07-10',
                'check_out' => '2026-07-12',
                'currency' => 'EUR',
            ], $overrides));
    }
}
