<?php

namespace Database\Factories;

use App\Models\ComplaintCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintCase>
 */
class ComplaintCaseFactory extends Factory
{
    protected $model = ComplaintCase::class;

    public function definition(): array
    {
        $guest = User::factory();
        $host = User::factory()->host();

        return [
            'complaint_number' => 'CMP-'.now()->year.'-'.$this->faker->unique()->numberBetween(100000, 999999),
            'guest_user_id' => $guest,
            'host_user_id' => $host,
            'reporter_user_id' => $guest,
            'against_user_id' => $host,
            'submitted_by_type' => 'guest',
            'against_type' => 'host',
            'complaint_type' => $this->faker->randomElement(['dirty_room', 'host_unresponsive', 'deposit_problem', 'other']),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high']),
            'status' => 'submitted',
            'description' => $this->faker->sentence(12),
            'desired_resolution_type' => 'fix_problem',
            'resolution_status' => 'not_started',
            'currency' => 'EUR',
        ];
    }
}
