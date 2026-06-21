<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCancellationPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceCancellationPolicy>
 */
class SleepingPlaceCancellationPolicyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'policy_type' => 'flexible',
            'title' => 'Flexible cancellation',
            'description' => null,
            'free_cancellation_until_days_before_check_in' => null,
            'free_cancellation_until_hours_before_check_in' => 24,
            'penalty_starts_hours_before_check_in' => 24,
            'first_night_non_refundable' => false,
            'cleaning_fee_refundable_before_check_in' => true,
            'service_fee_refundable' => false,
            'deposit_always_refundable_before_check_in' => true,
            'active' => true,
        ];
    }
}
