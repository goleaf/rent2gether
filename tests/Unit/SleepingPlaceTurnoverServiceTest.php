<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Availability\SleepingPlaceTurnoverService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SleepingPlaceTurnoverServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_day_turnover_is_allowed_when_required_gap_fits_before_next_check_in(): void
    {
        [$place, $booking] = $this->placeWithPreviousBooking();
        $place->turnoverRules()->create([
            'min_gap_minutes' => 240,
            'cleaning_required_between_guests' => true,
            'cleaning_gap_minutes' => 240,
            'inspection_required_after_checkout' => false,
            'inspection_gap_minutes' => 0,
            'same_day_turnover_allowed' => true,
            'same_day_turnover_requires_cleaning_done' => false,
            'same_day_turnover_requires_inspection_done' => false,
            'morning_checkout_evening_checkin_allowed' => true,
            'earliest_new_check_in_time' => '15:00',
            'latest_previous_check_out_time' => '11:00',
        ]);

        $this->assertTrue(app(SleepingPlaceTurnoverService::class)->canSameDayTurnover(
            $place->refresh(),
            $booking,
            CarbonImmutable::parse('2026-07-15'),
            '15:00',
        ));
    }

    public function test_same_day_turnover_is_blocked_when_required_gap_does_not_fit(): void
    {
        [$place, $booking] = $this->placeWithPreviousBooking();
        $place->turnoverRules()->create([
            'min_gap_minutes' => 240,
            'cleaning_required_between_guests' => true,
            'cleaning_gap_minutes' => 240,
            'same_day_turnover_allowed' => true,
            'same_day_turnover_requires_cleaning_done' => false,
            'same_day_turnover_requires_inspection_done' => false,
            'morning_checkout_evening_checkin_allowed' => true,
            'latest_previous_check_out_time' => '11:00',
        ]);

        $this->assertFalse(app(SleepingPlaceTurnoverService::class)->canSameDayTurnover(
            $place->refresh(),
            $booking,
            CarbonImmutable::parse('2026-07-15'),
            '14:00',
        ));
    }

    public function test_morning_checkout_evening_checkin_rule_can_block_same_day_turnover(): void
    {
        [$place, $booking] = $this->placeWithPreviousBooking();
        $place->turnoverRules()->create([
            'min_gap_minutes' => 0,
            'cleaning_required_between_guests' => false,
            'cleaning_gap_minutes' => 0,
            'inspection_required_after_checkout' => false,
            'inspection_gap_minutes' => 0,
            'same_day_turnover_allowed' => true,
            'same_day_turnover_requires_cleaning_done' => false,
            'same_day_turnover_requires_inspection_done' => false,
            'morning_checkout_evening_checkin_allowed' => false,
            'earliest_new_check_in_time' => '18:00',
            'latest_previous_check_out_time' => '08:00',
        ]);

        $this->assertFalse(app(SleepingPlaceTurnoverService::class)->canSameDayTurnover(
            $place->refresh(),
            $booking,
            CarbonImmutable::parse('2026-07-15'),
            '18:00',
        ));
    }

    /**
     * @return array{0: SleepingPlace, 1: Booking}
     */
    private function placeWithPreviousBooking(): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $guest = User::factory()->create();
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
            ->create(['status' => SleepingPlaceStatus::Active]);
        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($property)
            ->for($room)
            ->for($place)
            ->create([
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-15',
                'check_out_time' => '11:00',
                'status' => BookingStatus::Confirmed,
            ]);

        return [$place, $booking];
    }
}
