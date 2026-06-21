<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlacePricingSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlacePricingSetting>
 */
class SleepingPlacePricingSettingFactory extends Factory
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
            'currency' => 'EUR',
            'base_nightly_price' => fake()->randomFloat(2, 15, 80),
            'weekday_price' => null,
            'weekend_price' => null,
            'holiday_price' => null,
            'weekly_price' => null,
            'monthly_price' => null,
            'pricing_strategy' => SleepingPlacePricingSetting::STRATEGY_PER_NIGHT_WITH_DISCOUNTS,
            'weekend_days_json' => ['saturday', 'sunday'],
            'extra_guest_allowed' => false,
            'included_guests_count' => 1,
            'max_guests_count' => 1,
            'extra_guest_fee' => null,
            'early_check_in_mode' => SleepingPlacePricingSetting::TIME_MODE_NOT_ALLOWED,
            'early_check_in_fee' => null,
            'late_checkout_mode' => SleepingPlacePricingSetting::TIME_MODE_NOT_ALLOWED,
            'late_checkout_fee' => null,
            'cleaning_fee' => 10,
            'deposit_required' => true,
            'deposit_amount' => 50,
            'deposit_payable_now' => true,
            'deposit_refundable' => true,
            'guest_service_fee_type' => SleepingPlacePricingSetting::FEE_PERCENT,
            'guest_service_fee_value' => 5,
            'host_service_fee_type' => SleepingPlacePricingSetting::FEE_NONE,
            'host_service_fee_value' => 0,
            'tax_fee_type' => SleepingPlacePricingSetting::FEE_NONE,
            'tax_fee_value' => 0,
            'city_fee_type' => SleepingPlacePricingSetting::FEE_NONE,
            'city_fee_value' => 0,
            'active' => true,
        ];
    }
}
