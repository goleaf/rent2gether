<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceDatePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceDatePrice>
 */
class SleepingPlaceDatePriceFactory extends Factory
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
            'date' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'price' => fake()->randomFloat(2, 20, 120),
            'currency' => 'EUR',
            'price_type' => SleepingPlaceDatePrice::TYPE_MANUAL_OVERRIDE,
            'min_nights' => null,
            'max_nights' => null,
            'check_in_allowed' => true,
            'check_out_allowed' => true,
            'note' => null,
        ];
    }
}
