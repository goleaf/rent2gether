<?php

namespace Database\Factories;

use App\Models\DisputeCase;
use App\Models\DisputeDecision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisputeDecision>
 */
class DisputeDecisionFactory extends Factory
{
    protected $model = DisputeDecision::class;

    public function definition(): array
    {
        return [
            'dispute_case_id' => DisputeCase::factory(),
            'decision_type' => 'system_rule',
            'resolution_type' => 'no_action',
            'currency' => 'EUR',
            'decided_by_type' => 'system_rule',
            'status' => 'draft',
        ];
    }
}
