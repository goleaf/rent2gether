<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceCalendarSetting>
 */
class SleepingPlaceCalendarSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'default_status' => 'available',
            'default_price' => 20,
            'currency' => 'EUR',
            'min_nights' => 1,
            'max_nights' => 30,
            'weekly_discount_percent' => null,
            'monthly_discount_percent' => null,
            'cleaning_gap_hours' => 0,
            'cleaning_gap_days' => 0,
            'instant_booking_enabled' => false,
            'requires_host_approval' => true,
            'can_extend' => true,
            'same_day_check_in_allowed' => true,
            'same_day_turnover_allowed' => false,
            'check_in_time_from' => '14:00',
            'check_in_time_until' => '22:00',
            'check_out_time_until' => '11:00',
        ];
    }
}
