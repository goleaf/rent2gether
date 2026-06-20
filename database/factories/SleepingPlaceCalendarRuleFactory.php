<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceCalendarRule>
 */
class SleepingPlaceCalendarRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'rule_type' => 'weekend_price',
            'starts_at' => null,
            'ends_at' => null,
            'weekdays_json' => [5, 6],
            'status' => null,
            'price' => 25,
            'min_nights' => null,
            'max_nights' => null,
            'check_in_allowed' => null,
            'check_out_allowed' => null,
            'priority' => 0,
        ];
    }
}
