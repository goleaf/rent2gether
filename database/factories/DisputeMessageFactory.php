<?php

namespace Database\Factories;

use App\Models\DisputeCase;
use App\Models\DisputeMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisputeMessage>
 */
class DisputeMessageFactory extends Factory
{
    protected $model = DisputeMessage::class;

    public function definition(): array
    {
        return [
            'dispute_case_id' => DisputeCase::factory(),
            'user_id' => User::factory(),
            'message_type' => 'statement',
            'message' => $this->faker->sentence(12),
            'visibility' => 'guest_and_host',
        ];
    }
}
