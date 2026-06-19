<?php

namespace Database\Factories;

use App\Enums\PriceRuleType;
use App\Models\PriceRule;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceRule>
 */
class PriceRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'type' => PriceRuleType::Weekend->value,
            'starts_on' => now()->addWeek()->toDateString(),
            'ends_on' => now()->addWeeks(2)->toDateString(),
            'price' => 35,
            'currency' => 'EUR',
            'min_nights' => 1,
            'days_of_week_json' => [5, 6],
            'priority' => 10,
            'status' => 'active',
        ];
    }
}
