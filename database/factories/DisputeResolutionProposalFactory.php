<?php

namespace Database\Factories;

use App\Models\DisputeCase;
use App\Models\DisputeResolutionProposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisputeResolutionProposal>
 */
class DisputeResolutionProposalFactory extends Factory
{
    protected $model = DisputeResolutionProposal::class;

    public function definition(): array
    {
        return [
            'dispute_case_id' => DisputeCase::factory(),
            'resolution_type' => 'no_action',
            'amount' => 0,
            'currency' => 'EUR',
            'description' => $this->faker->sentence(10),
            'status' => 'offered',
        ];
    }
}
