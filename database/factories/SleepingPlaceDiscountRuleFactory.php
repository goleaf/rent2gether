<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceDiscountRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceDiscountRule>
 */
class SleepingPlaceDiscountRuleFactory extends Factory
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
            'discount_type' => SleepingPlaceDiscountRule::TYPE_WEEKLY,
            'name' => fake()->words(3, true),
            'value_type' => SleepingPlaceDiscountRule::VALUE_PERCENT,
            'value' => 10,
            'min_nights' => 7,
            'max_nights' => null,
            'min_days_before_check_in' => null,
            'max_days_before_check_in' => null,
            'new_guest_only' => false,
            'allow_stacking' => false,
            'priority' => 10,
            'active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (): array => [
            'discount_type' => SleepingPlaceDiscountRule::TYPE_MONTHLY,
            'value' => 25,
            'min_nights' => 30,
            'priority' => 20,
        ]);
    }

    public function longStay(int $minNights = 14, float $value = 15): static
    {
        return $this->state(fn (): array => [
            'discount_type' => SleepingPlaceDiscountRule::TYPE_LONG_STAY,
            'value' => $value,
            'min_nights' => $minNights,
            'priority' => 15,
        ]);
    }
}
