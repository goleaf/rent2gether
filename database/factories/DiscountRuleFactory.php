<?php

namespace Database\Factories;

use App\Enums\DiscountRuleType;
use App\Models\DiscountRule;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountRule>
 */
class DiscountRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'type' => DiscountRuleType::Weekly->value,
            'min_nights' => 7,
            'percent' => 10,
            'amount' => null,
            'starts_on' => null,
            'ends_on' => null,
            'status' => 'active',
        ];
    }
}
