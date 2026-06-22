<?php

namespace Database\Factories;

use App\Models\ComplaintCase;
use App\Models\ComplaintResolutionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintResolutionOption>
 */
class ComplaintResolutionOptionFactory extends Factory
{
    protected $model = ComplaintResolutionOption::class;

    public function definition(): array
    {
        return [
            'complaint_case_id' => ComplaintCase::factory(),
            'resolution_type' => 'fix_problem',
            'status' => 'offered',
            'description' => $this->faker->sentence(8),
            'amount' => 0,
            'currency' => 'EUR',
            'offered_at' => now(),
        ];
    }
}
