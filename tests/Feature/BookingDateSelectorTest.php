<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Booking\BookingDateSelector;
use App\Models\AvailabilityDay;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use Tests\TestCase;

class BookingDateSelectorTest extends TestCase
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

    public function test_booking_date_selector_renders_and_calculates_valid_dates(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 20,
            'cleaning_fee' => 5,
            'deposit_amount' => 30,
        ]);

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->assertSee(Lang::get('booking.date_selector.title', [], 'en'))
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15')
            ->assertHasNoErrors()
            ->assertSet('quote.nights_count', 5)
            ->assertSet('quote.calendar_days_count', 6)
            ->assertSet('quote.total_amount', 140.0)
            ->assertSee(Lang::get('booking.date_selector.price.title', [], 'en'))
            ->assertSee(Lang::get('booking.price_lines.deposit', [], 'en'));
    }

    public function test_same_day_checkout_is_invalid(): void
    {
        [$guest, $place] = $this->sleepingPlace();

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-10')
            ->assertHasErrors(['checkOut'])
            ->assertSet('quote', null);
    }

    public function test_checkout_before_checkin_is_invalid(): void
    {
        [$guest, $place] = $this->sleepingPlace();

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-12')
            ->set('checkOut', '2026-07-10')
            ->assertHasErrors(['checkOut'])
            ->assertSet('quote', null);
    }

    public function test_minimum_nights_are_enforced(): void
    {
        [$guest, $place] = $this->sleepingPlace(['min_nights' => 3]);

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-12')
            ->assertHasErrors(['checkIn'])
            ->assertSee(trans_choice('booking.date_selector.errors.min_nights', 3, ['count' => 3]))
            ->assertSet('quote', null);
    }

    public function test_maximum_nights_are_enforced(): void
    {
        [$guest, $place] = $this->sleepingPlace(['max_nights' => 4]);

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15')
            ->assertHasErrors(['checkOut'])
            ->assertSee(trans_choice('booking.date_selector.errors.max_nights', 4, ['count' => 4]))
            ->assertSet('quote', null);
    }

    public function test_guest_count_cannot_exceed_sleeping_place_capacity(): void
    {
        [$guest, $place] = $this->sleepingPlace(['max_guests' => 1]);

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('guestsCount', 2)
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-12')
            ->assertHasErrors(['guestsCount'])
            ->assertSee(trans_choice('booking.date_selector.errors.max_guests', 1, ['count' => 1]))
            ->assertSet('quote', null);
    }

    public function test_unavailable_date_inside_range_is_invalid(): void
    {
        [$guest, $place] = $this->sleepingPlace();
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::Repair,
        ]);

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-12')
            ->assertHasErrors(['checkIn'])
            ->assertSet('quote', null)
            ->assertSet('unavailableDates.0', '2026-07-11')
            ->assertSee(Lang::get('booking.date_selector.unavailable.title', [], 'en'));
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
                'base_price_per_night' => 20,
                'weekly_price' => null,
                'monthly_price' => null,
                'weekend_price' => null,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => null,
                'max_guests' => 1,
            ], $attributes));

        return [$guest, $place];
    }
}
