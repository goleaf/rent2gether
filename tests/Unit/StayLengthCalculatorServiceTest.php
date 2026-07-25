<?php

namespace Tests\Unit;

use App\Data\Bookings\BookingDateSelectionData;
use App\Services\Bookings\BookingDateSelectionService;
use App\Services\Bookings\StayLengthCalculatorService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class StayLengthCalculatorServiceTest extends TestCase
{
    public function test_half_open_nightly_range_counts_billable_and_presence_days(): void
    {
        $service = app(StayLengthCalculatorService::class);
        $checkIn = CarbonImmutable::parse('2026-07-10');
        $checkOut = CarbonImmutable::parse('2026-07-13');

        $this->assertSame(3, $service->calculateNights($checkIn, $checkOut));
        $this->assertSame(3, $service->calculateChargeableDays($checkIn, $checkOut));
        $this->assertSame(4, $service->calculateCalendarPresenceDays($checkIn, $checkOut));
    }

    public function test_basic_date_order_validation_blocks_reverse_and_same_day_nightly_ranges(): void
    {
        $service = app(StayLengthCalculatorService::class);

        $this->assertSame('checkout_before_checkin', $service->validateBasicDateOrder([
            'check_in_date' => '2026-07-13',
            'check_out_date' => '2026-07-10',
        ])[0]['validation_key']);

        $this->assertSame('checkout_same_day_not_allowed', $service->validateBasicDateOrder([
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-10',
        ])[0]['validation_key']);

        $this->assertSame([], $service->validateBasicDateOrder([
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-11',
        ]));
    }

    public function test_date_selection_service_returns_compact_selection_data(): void
    {
        $selection = app(BookingDateSelectionService::class)->selectionData([
            'check_in_date' => '2026-07-10',
            'check_in_time' => '14:30',
            'check_out_date' => '2026-07-13',
            'check_out_time' => '10:00',
            'early_check_in_requested' => true,
            'late_check_out_requested' => false,
            'flexible_check_in' => true,
            'flexible_check_out' => false,
            'requires_host_time_approval' => true,
            'check_in_comment' => 'Can arrive after bus.',
            'check_out_comment' => 'No special request.',
        ]);

        $this->assertInstanceOf(BookingDateSelectionData::class, $selection);
        $this->assertSame(3, $selection->nightsCount);
        $this->assertSame(3, $selection->stayDaysCount);
        $this->assertSame(4, $selection->calendarPresenceDaysCount);
        $this->assertSame([
            'check_in_date' => '2026-07-10',
            'check_in_time' => '14:30',
            'check_out_date' => '2026-07-13',
            'check_out_time' => '10:00',
            'nights_count' => 3,
            'stay_days_count' => 3,
            'chargeable_days_count' => 3,
            'calendar_presence_days_count' => 4,
            'early_check_in_requested' => true,
            'late_check_out_requested' => false,
            'flexible_check_in' => true,
            'flexible_check_out' => false,
            'requires_host_time_approval' => true,
            'check_in_comment' => 'Can arrive after bus.',
            'check_out_comment' => 'No special request.',
        ], $selection->toArray());
    }
}
