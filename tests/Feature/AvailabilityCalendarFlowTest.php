<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Booking\AvailabilityChecker;
use App\Livewire\Shell\HostCalendarPage;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use Tests\TestCase;

class AvailabilityCalendarFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-01 09:00:00');
        CarbonImmutable::setTestNow('2026-07-01 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_host_calendar_page_renders_real_calendar_surface(): void
    {
        [$host, $place] = $this->hostSleepingPlace();

        $this->actingAs($host)
            ->get(route('host.calendar', ['locale' => 'en']))
            ->assertOk()
            ->assertSeeLivewire(HostCalendarPage::class)
            ->assertSee(Lang::get('shell.pages.host.calendar.title', [], 'en'))
            ->assertSee(Lang::get('availability.calendar.fields.property', [], 'en'))
            ->assertSee(Lang::get('availability.calendar.fields.room', [], 'en'))
            ->assertSee(Lang::get('availability.calendar.fields.sleeping_place', [], 'en'))
            ->assertSee(Lang::get('availability.calendar.summary.occupancy', [], 'en'))
            ->assertSee($place->display_name);
    }

    public function test_host_can_open_dates(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-10',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);

        Livewire::actingAs($host)
            ->test(HostCalendarPage::class)
            ->set('selectedSleepingPlaceId', $place->id)
            ->set('rangeStart', '2026-07-10')
            ->set('rangeEnd', '2026-07-10')
            ->call('openRange')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10 00:00:00',
            'status' => AvailabilityStatus::Available->value,
        ]);
    }

    public function test_host_can_close_dates(): void
    {
        [$host, $place] = $this->hostSleepingPlace();

        Livewire::actingAs($host)
            ->test(HostCalendarPage::class)
            ->set('selectedSleepingPlaceId', $place->id)
            ->set('rangeStart', '2026-07-10')
            ->set('rangeEnd', '2026-07-11')
            ->set('bulkStatus', AvailabilityStatus::BlockedByHost->value)
            ->call('closeRange')
            ->assertHasNoErrors()
            ->assertSee(Lang::get('availability.calendar.flash.saved', ['count' => 2, 'skipped' => 0], 'en'));

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10 00:00:00',
            'status' => AvailabilityStatus::BlockedByHost->value,
        ]);
    }

    public function test_host_can_set_price_override(): void
    {
        [$host, $place] = $this->hostSleepingPlace();

        Livewire::actingAs($host)
            ->test(HostCalendarPage::class)
            ->set('selectedSleepingPlaceId', $place->id)
            ->set('rangeStart', '2026-07-10')
            ->set('rangeEnd', '2026-07-11')
            ->set('bulkStatus', AvailabilityStatus::Available->value)
            ->set('priceOverride', 31.50)
            ->set('minNightsOverride', 2)
            ->set('maxNightsOverride', 10)
            ->set('checkInAllowed', false)
            ->set('checkOutAllowed', true)
            ->set('note', 'Private repair note')
            ->call('applyRange')
            ->assertHasNoErrors()
            ->assertSee(Lang::get('availability.calendar.flash.saved', ['count' => 2, 'skipped' => 0], 'en'));

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10 00:00:00',
            'status' => AvailabilityStatus::Available->value,
            'price_override' => 31.50,
            'min_nights_override' => 2,
            'max_nights_override' => 10,
            'check_in_allowed' => false,
            'check_out_allowed' => true,
            'note' => 'Private repair note',
        ]);
    }

    public function test_host_can_mark_repair_and_cleaning(): void
    {
        [$host, $place] = $this->hostSleepingPlace();

        Livewire::actingAs($host)
            ->test(HostCalendarPage::class)
            ->set('selectedSleepingPlaceId', $place->id)
            ->set('rangeStart', '2026-07-12')
            ->set('rangeEnd', '2026-07-12')
            ->call('markRepairRange')
            ->assertHasNoErrors()
            ->set('rangeStart', '2026-07-13')
            ->set('rangeEnd', '2026-07-13')
            ->call('markCleaningRange')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-12 00:00:00',
            'status' => AvailabilityStatus::Repair->value,
        ]);
        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-13 00:00:00',
            'status' => AvailabilityStatus::Cleaning->value,
        ]);
    }

    public function test_host_calendar_does_not_overwrite_booking_hold_dates(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        $booking = Booking::factory()
            ->for(User::factory()->create(), 'guest')
            ->for($host, 'host')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create([
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-11',
                'status' => BookingStatus::Confirmed,
            ]);

        app(AvailabilityService::class)->blockForBooking($booking);

        Livewire::actingAs($host)
            ->test(HostCalendarPage::class)
            ->set('selectedSleepingPlaceId', $place->id)
            ->set('rangeStart', '2026-07-10')
            ->set('rangeEnd', '2026-07-10')
            ->set('bulkStatus', AvailabilityStatus::Available->value)
            ->call('applyRange')
            ->assertHasNoErrors()
            ->assertSee(Lang::get('availability.calendar.flash.saved', ['count' => 0, 'skipped' => 1], 'en'));

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10 00:00:00',
            'booking_id' => $booking->id,
            'status' => AvailabilityStatus::Booked->value,
        ]);
    }

    public function test_booking_appears_on_host_calendar(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        Booking::factory()
            ->for(User::factory()->create(['name' => 'Calendar Guest']), 'guest')
            ->for($host, 'host')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create([
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-12',
                'guests_count' => 1,
                'status' => BookingStatus::Confirmed,
            ]);

        Livewire::actingAs($host)
            ->test(HostCalendarPage::class)
            ->set('selectedSleepingPlaceId', $place->id)
            ->set('month', '2026-07')
            ->assertSee('Calendar Guest')
            ->assertSee(Lang::get('availability.calendar.booking_guest', ['guest' => 'Calendar Guest', 'count' => 1], 'en'));
    }

    public function test_another_host_cannot_edit_calendar(): void
    {
        [$host] = $this->hostSleepingPlace();
        [, $otherPlace] = $this->hostSleepingPlace();

        Livewire::actingAs($host)
            ->test(HostCalendarPage::class)
            ->set('selectedSleepingPlaceId', $otherPlace->id)
            ->set('rangeStart', '2026-07-10')
            ->set('rangeEnd', '2026-07-10')
            ->set('bulkStatus', AvailabilityStatus::BlockedByHost->value)
            ->call('applyRange')
            ->assertHasErrors(['selectedSleepingPlaceId']);

        $this->assertDatabaseMissing('availability_days', [
            'sleeping_place_id' => $otherPlace->id,
            'date' => '2026-07-10 00:00:00',
            'status' => AvailabilityStatus::BlockedByHost->value,
        ]);
    }

    public function test_occupancy_calculation_for_selected_scope(): void
    {
        [$host, $place] = $this->hostSleepingPlace();
        Booking::factory()
            ->for(User::factory()->create(), 'guest')
            ->for($host, 'host')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create([
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-15',
                'status' => BookingStatus::Confirmed,
            ]);

        Livewire::actingAs($host)
            ->test(HostCalendarPage::class)
            ->set('selectedPropertyId', $place->property_id)
            ->set('selectedRoomId', $place->room_id)
            ->set('selectedSleepingPlaceId', $place->id)
            ->set('month', '2026-07')
            ->assertSet('summary.occupied_nights', 5)
            ->assertSet('summary.occupancy_percentage', 16);
    }

    public function test_guest_availability_checker_reports_available_dates(): void
    {
        [, $place] = $this->hostSleepingPlace();

        Livewire::test(AvailabilityChecker::class, ['sleepingPlaceId' => $place->id])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-12')
            ->call('checkAvailability')
            ->assertHasNoErrors()
            ->assertSet('result.available', true)
            ->assertSee(Lang::get('availability.checker.available_title', [], 'en'));
    }

    public function test_guest_availability_checker_returns_unavailable_dates_and_nearest_ranges(): void
    {
        [, $place] = $this->hostSleepingPlace();
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-10',
            'status' => AvailabilityStatus::Repair,
        ]);

        Livewire::test(AvailabilityChecker::class, ['sleepingPlaceId' => $place->id])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-12')
            ->call('checkAvailability')
            ->assertHasNoErrors()
            ->assertSet('result.available', false)
            ->assertSet('result.unavailable_dates.0', '2026-07-10')
            ->assertSet('result.nearest_ranges.0.check_in', '2026-07-11')
            ->assertSee(Lang::get('availability.checker.unavailable_title', [], 'en'));
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
                'title' => 'Calendar property',
            ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Calendar room',
        ]);
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'display_name' => 'Calendar sleeping place',
                'place_number' => 'A1',
            ]);
        $place->translations()->create([
            'locale' => 'en',
            'title' => 'Calendar sleeping place',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'Календарное спальное место',
        ]);

        return [$host, $place];
    }
}
