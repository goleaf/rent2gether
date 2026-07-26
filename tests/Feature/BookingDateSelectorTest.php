<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Booking\BookingDateSelector;
use App\Livewire\Bookings\Dates\DateSelectionPanel;
use App\Models\AvailabilityDay;
use App\Models\BookingQuote;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
            ->assertViewHas('quote', function (?array $quote): bool {
                return is_array($quote)
                    && $quote['nights_count'] === 5
                    && $quote['calendar_days_count'] === 6
                    && $quote['total_amount'] === 140.0;
            })
            ->assertSee(Lang::get('booking.date_selector.price.title', [], 'en'))
            ->assertSee(Lang::get('booking.price_lines.deposit', [], 'en'));
    }

    public function test_booking_date_selector_keeps_quote_out_of_livewire_public_state(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 20,
            'cleaning_fee' => 5,
            'deposit_amount' => 30,
        ]);

        $component = Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15')
            ->assertHasNoErrors()
            ->assertViewHas('quote', function (?array $quote): bool {
                return is_array($quote)
                    && $quote['nights_count'] === 5
                    && $quote['calendar_days_count'] === 6
                    && $quote['total_amount'] === 140.0;
            })
            ->refresh()
            ->assertHasNoErrors()
            ->assertViewHas('quote', function (?array $quote): bool {
                return is_array($quote)
                    && $quote['nights_count'] === 5
                    && $quote['calendar_days_count'] === 6
                    && $quote['total_amount'] === 140.0;
            });

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('sleepingPlaceId', $encodedSnapshot);
        $this->assertStringNotContainsString('quote', $encodedSnapshot);
        $this->assertStringNotContainsString('unavailableDates', $encodedSnapshot);
        $this->assertStringNotContainsString('nearestRanges', $encodedSnapshot);
        $this->assertLessThan(16_000, strlen($encodedSnapshot), 'Booking date selector snapshot should keep quote details out of public state.');
    }

    public function test_booking_date_selector_reuses_prefetched_availability_days_for_quote_preview(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 20,
            'cleaning_fee' => 5,
            'deposit_amount' => 30,
        ]);
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-12',
            'status' => AvailabilityStatus::Available,
            'price_override' => 25,
            'min_nights_override' => 2,
        ]);

        $availabilityDaySelects = 0;
        DB::listen(static function ($query) use (&$availabilityDaySelects): void {
            $sql = strtolower($query->sql);

            if (str_starts_with($sql, 'select') && str_contains($sql, 'from "availability_days"')) {
                $availabilityDaySelects++;
            }
        });

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-15')
            ->assertHasNoErrors()
            ->assertViewHas('quote', function (?array $quote): bool {
                return is_array($quote)
                    && $quote['date_override_amount'] === 5.0
                    && $quote['total_amount'] === 145.25;
            });

        $this->assertLessThanOrEqual(2, $availabilityDaySelects, 'Booking date selector should reuse prefetched availability days for stay-limit and pricing preview work.');
    }

    public function test_same_day_checkout_is_invalid(): void
    {
        [$guest, $place] = $this->sleepingPlace();

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-10')
            ->assertHasErrors(['checkOut'])
            ->assertViewHas('quote', fn (?array $quote): bool => $quote === null);
    }

    public function test_checkout_before_checkin_is_invalid(): void
    {
        [$guest, $place] = $this->sleepingPlace();

        Livewire::actingAs($guest)
            ->test(BookingDateSelector::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-12')
            ->set('checkOut', '2026-07-10')
            ->assertHasErrors(['checkOut'])
            ->assertViewHas('quote', fn (?array $quote): bool => $quote === null);
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
            ->assertViewHas('quote', fn (?array $quote): bool => $quote === null);
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
            ->assertViewHas('quote', fn (?array $quote): bool => $quote === null);
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
            ->assertViewHas('quote', fn (?array $quote): bool => $quote === null);
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
            ->assertViewHas('quote', fn (?array $quote): bool => $quote === null)
            ->assertViewHas('unavailableDates', function (array $dates): bool {
                return $dates === ['2026-07-11'];
            })
            ->assertSee(Lang::get('booking.date_selector.unavailable.title', [], 'en'));
    }

    public function test_date_selection_panel_exposes_smart_checkout_calendar_to_view(): void
    {
        [$guest, $place] = $this->sleepingPlace();

        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-12',
            'status' => AvailabilityStatus::Repair,
        ]);

        Livewire::actingAs($guest)
            ->test(DateSelectionPanel::class, ['sleepingPlace' => $place->id])
            ->set('checkInDate', '2026-07-10')
            ->assertSet('quoteId', null)
            ->assertViewHas('availableCheckoutDates', function (array $dates): bool {
                return collect($dates)->pluck('check_out')->all() === ['2026-07-11', '2026-07-12'];
            })
            ->assertViewHas('checkoutCalendar', function (array $calendar): bool {
                $blockedCheckout = collect($calendar['unavailable_checkout_dates'])->firstWhere('check_out', '2026-07-13');

                return $calendar['earliest_checkout_date'] === '2026-07-11'
                    && $calendar['latest_checkout_date'] === '2026-07-12'
                    && is_array($blockedCheckout)
                    && in_array('repair', $blockedCheckout['reasons'], true);
            });
    }

    public function test_date_selection_panel_reuses_sleeping_place_lookup_for_initial_quote_and_checkout_calendar(): void
    {
        [$guest, $place] = $this->sleepingPlace([
            'base_price_per_night' => 20,
            'cleaning_fee' => 5,
            'deposit_amount' => 30,
        ]);

        $sleepingPlaceLookups = 0;
        DB::listen(static function ($query) use (&$sleepingPlaceLookups): void {
            $sql = strtolower($query->sql);

            if (
                str_contains($sql, 'from "sleeping_places"')
                && str_contains($sql, 'where "sleeping_places"."id" = ?')
                && str_contains($sql, 'limit 1')
            ) {
                $sleepingPlaceLookups++;
            }
        });

        Livewire::actingAs($guest)
            ->test(DateSelectionPanel::class, [
                'sleepingPlace' => $place->id,
                'checkInDate' => '2026-07-10',
                'checkOutDate' => '2026-07-15',
            ])
            ->assertHasNoErrors()
            ->assertViewHas('quote', fn (?BookingQuote $quote): bool => $quote instanceof BookingQuote)
            ->assertViewHas('availableCheckoutDates', fn (array $dates): bool => $dates !== []);

        $this->assertLessThanOrEqual(1, $sleepingPlaceLookups, 'Date selection panel should reuse the selected sleeping place lookup across initial quote and checkout-calendar rendering.');
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
