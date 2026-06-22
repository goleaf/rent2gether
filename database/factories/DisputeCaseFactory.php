<?php

namespace Database\Factories;

use App\Models\DisputeCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisputeCase>
 */
class DisputeCaseFactory extends Factory
{
    protected $model = DisputeCase::class;

    public function definition(): array
    {
        $guest = User::factory();
        $host = User::factory()->host();

        return [
            'dispute_number' => 'DSP-'.now()->year.'-'.$this->faker->unique()->numberBetween(100000, 999999),
            'guest_user_id' => $guest,
            'host_user_id' => $host,
            'opened_by_user_id' => $guest,
            'dispute_type' => $this->faker->randomElement(['refund_dispute', 'deposit_dispute', 'other']),
            'severity' => 'medium',
            'status' => 'opened',
            'description' => $this->faker->sentence(12),
            'amount_disputed' => 0,
            'currency' => 'EUR',
        ];
    }
}
