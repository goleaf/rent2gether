<?php

namespace Database\Factories;

use App\Models\SleepingPlaceCancellationPolicy;
use App\Models\SleepingPlaceCancellationPolicyRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceCancellationPolicyRule>
 */
class SleepingPlaceCancellationPolicyRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_cancellation_policy_id' => SleepingPlaceCancellationPolicy::factory(),
            'rule_key' => 'free_before_deadline',
            'applies_when' => 'before_free_cancellation_deadline',
            'refund_percent' => 100,
            'fixed_penalty_amount' => null,
            'currency' => 'EUR',
            'description' => null,
            'sort_order' => 0,
        ];
    }
}
