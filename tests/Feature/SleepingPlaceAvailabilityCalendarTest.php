<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Livewire\Bookings\Availability\SleepingPlaceCalendar;
use App\Livewire\Host\Availability\HostTurnoverRulesForm;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Services\Availability\CalendarBulkActionService;
use App\Services\Availability\SleepingPlaceCalendarBlockService;
use App\Services\Availability\SleepingPlaceCalendarStatusService;
use App\Services\Availability\SleepingPlaceDateLockService;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SleepingPlaceAvailabilityCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_date_locks_use_half_open_range_and_skip_checkout_date(): void
    {
        $place = $this->sleepingPlace();
        $booking = $this->booking($place, '2026-07-10', '2026-07-15');

        app(SleepingPlaceDateLockService::class)->createLocksForBooking($booking);

        $this->assertSame(
            ['2026-07-10', '2026-07-11', '2026-07-12', '2026-07-13', '2026-07-14'],
            $place->bookingDateLocks()->orderBy('date')->pluck('date')->map->toDateString()->all(),
        );
        $this->assertDatabaseMissing('sleeping_place_booking_date_locks', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-15',
            'status' => 'active',
        ]);
    }

    public function test_booking_date_locks_prefetch_existing_locks_once_for_long_range(): void
    {
        $place = $this->sleepingPlace();
        $booking = $this->booking($place, '2026-08-01', '2026-08-31');
        $lockSelects = [];

        DB::listen(function ($query) use (&$lockSelects): void {
            $sql = strtolower($query->sql);

            if (str_starts_with($sql, 'select') && str_contains($sql, 'sleeping_place_booking_date_locks')) {
                $lockSelects[] = $query->sql;
            }
        });

        app(SleepingPlaceDateLockService::class)->createLocksForBooking($booking);
        $lockSelectCount = count($lockSelects);

        $this->assertSame(30, $place->bookingDateLocks()->where('status', 'active')->count());
        $this->assertLessThanOrEqual(1, $lockSelectCount);
    }

    public function test_active_date_lock_unique_index_prevents_double_booking_race(): void
    {
        $place = $this->sleepingPlace();

        SleepingPlaceBookingDateLock::factory()->create([
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10',
            'status' => 'active',
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        SleepingPlaceBookingDateLock::factory()->create([
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10',
            'status' => 'active',
        ]);
    }

    public function test_booking_one_sleeping_place_does_not_block_another_sleeping_place_in_same_room(): void
    {
        $host = User::factory()->host()->create();
        $property = Property::factory()->create(['host_user_id' => $host->id]);
        $room = Room::factory()->for($property)->create(['user_id' => $host->id]);
        $first = SleepingPlace::factory()->for($property)->for($room)->create(['user_id' => $host->id]);
        $second = SleepingPlace::factory()->for($property)->for($room)->create(['user_id' => $host->id]);
        $booking = $this->booking($first, '2026-07-10', '2026-07-15');

        app(SleepingPlaceDateLockService::class)->createLocksForBooking($booking);

        $availability = app(AvailabilityService::class);

        $this->assertFalse($availability->isAvailable($first, '2026-07-10', '2026-07-15'));
        $this->assertTrue($availability->isAvailable($second, '2026-07-10', '2026-07-15'));
    }

    public function test_overlap_ranges_block_and_checkout_boundary_respects_turnover_rules(): void
    {
        $place = $this->sleepingPlace();
        $booking = $this->booking($place, '2026-07-10', '2026-07-15');
        app(SleepingPlaceDateLockService::class)->createLocksForBooking($booking);
        $availability = app(AvailabilityService::class);

        $this->assertFalse($availability->isAvailable($place, '2026-07-09', '2026-07-11'));
        $this->assertFalse($availability->isAvailable($place, '2026-07-10', '2026-07-12'));
        $this->assertFalse($availability->isAvailable($place, '2026-07-14', '2026-07-16'));
        $this->assertFalse($availability->isAvailable($place, '2026-07-11', '2026-07-15'));
        $this->assertTrue($availability->isAvailable($place, '2026-07-08', '2026-07-10'));

        $place->turnoverRules()->create([
            'same_day_turnover_allowed' => false,
        ]);

        $this->assertFalse($availability->isAvailable($place, '2026-07-15', '2026-07-16'));

        $place->turnoverRules()->update([
            'min_gap_minutes' => 240,
            'cleaning_required_between_guests' => true,
            'cleaning_gap_minutes' => 240,
            'inspection_required_after_checkout' => false,
            'inspection_gap_minutes' => 0,
            'same_day_turnover_allowed' => true,
            'same_day_turnover_requires_cleaning_done' => false,
            'same_day_turnover_requires_inspection_done' => false,
            'earliest_new_check_in_time' => '17:00',
            'latest_previous_check_out_time' => '11:00',
        ]);
        $booking->update(['check_out_time' => '11:00']);

        $this->assertTrue($availability->isAvailable($place->refresh(), '2026-07-15', '2026-07-16'));
    }

    public function test_expired_payment_pending_locks_are_released(): void
    {
        $place = $this->sleepingPlace();

        SleepingPlaceBookingDateLock::factory()->paymentPending()->create([
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-10',
            'expires_at' => now()->subMinute(),
        ]);

        $this->assertSame(1, app(SleepingPlaceDateLockService::class)->expireOldLocks());
        $this->assertTrue(app(AvailabilityService::class)->isAvailable($place, '2026-07-10', '2026-07-11'));
    }

    public function test_blocks_and_request_only_statuses_resolve_for_private_and_public_calendar(): void
    {
        $repairPlace = $this->sleepingPlace();
        app(SleepingPlaceCalendarBlockService::class)->createRepairBlock($repairPlace, [
            'starts_at' => '2026-07-10',
            'ends_at' => '2026-07-12',
        ]);

        $this->assertFalse(app(AvailabilityService::class)->isAvailable($repairPlace, '2026-07-10', '2026-07-11'));

        $complaintPlace = $this->sleepingPlace();
        app(SleepingPlaceCalendarBlockService::class)->createComplaintBlock($complaintPlace, [
            'starts_at' => '2026-07-10',
            'ends_at' => '2026-07-12',
        ]);

        $this->assertSame(
            'unavailable',
            app(SleepingPlaceCalendarStatusService::class)->getPublicStatus($complaintPlace, CarbonImmutable::parse('2026-07-10')),
        );

        $requestOnlyPlace = $this->sleepingPlace();
        $requestOnlyPlace->calendarDays()->create([
            'date' => '2026-07-10',
            'status' => 'request_only',
            'check_in_allowed' => true,
            'check_out_allowed' => true,
        ]);

        $result = app(AvailabilityService::class)->canBookRange(
            User::factory()->create(),
            $requestOnlyPlace,
            CarbonImmutable::parse('2026-07-10'),
            CarbonImmutable::parse('2026-07-11'),
        );

        $this->assertTrue($result['available']);
        $this->assertTrue($result['request_only']);
        $this->assertFalse($result['can_instant_book']);
    }

    public function test_bulk_close_dates_does_not_overwrite_active_locks(): void
    {
        $host = User::factory()->host()->create();
        $place = $this->sleepingPlace($host);

        SleepingPlaceBookingDateLock::factory()->create([
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-11',
            'status' => 'active',
        ]);

        $changed = app(CalendarBulkActionService::class)->bulkCloseDates(
            $host,
            $place,
            CarbonImmutable::parse('2026-07-10'),
            CarbonImmutable::parse('2026-07-13'),
            'closed_by_host',
        );

        $this->assertCount(2, $changed);
        $this->assertDatabaseMissing('sleeping_place_calendar_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-07-11',
            'status' => 'closed_by_host',
        ]);
    }

    public function test_availability_livewire_components_render_in_english_and_russian(): void
    {
        $place = $this->sleepingPlace();

        app()->setLocale('en');

        Livewire::test(HostTurnoverRulesForm::class, ['sleepingPlaceId' => $place->id])
            ->assertSee('Turnover rules');

        app()->setLocale('ru');

        Livewire::test(SleepingPlaceCalendar::class, ['sleepingPlaceId' => $place->id])
            ->assertSee('Доступность');
    }

    private function sleepingPlace(?User $host = null): SleepingPlace
    {
        $host ??= User::factory()->host()->create();
        $property = Property::factory()->create(['host_user_id' => $host->id]);
        $room = Room::factory()->for($property)->create(['user_id' => $host->id]);

        return SleepingPlace::factory()->for($property)->for($room)->create(['user_id' => $host->id]);
    }

    private function booking(SleepingPlace $place, string $checkIn, string $checkOut): Booking
    {
        return Booking::factory()->for($place, 'sleepingPlace')->create([
            'sleeping_place_id' => $place->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'host_user_id' => $place->user_id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'status' => BookingStatus::Confirmed->value,
        ]);
    }
}
