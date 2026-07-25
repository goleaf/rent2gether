<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTurnoverRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceTurnoverRule>
 */
class SleepingPlaceTurnoverRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'min_gap_minutes' => 0,
            'cleaning_required_between_guests' => true,
            'cleaning_gap_minutes' => 0,
            'inspection_required_after_checkout' => false,
            'inspection_gap_minutes' => 0,
            'same_day_turnover_allowed' => false,
            'morning_checkout_evening_checkin_allowed' => true,
            'same_day_turnover_requires_cleaning_done' => true,
            'same_day_turnover_requires_inspection_done' => false,
            'earliest_new_check_in_time' => '15:00',
            'latest_previous_check_out_time' => '11:00',
        ];
    }

    public function sameDayAllowed(): static
    {
        return $this->state([
            'same_day_turnover_allowed' => true,
            'morning_checkout_evening_checkin_allowed' => true,
            'same_day_turnover_requires_cleaning_done' => false,
            'same_day_turnover_requires_inspection_done' => false,
        ]);
    }
}
