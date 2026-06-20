<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserVerification>
 */
class UserVerificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'verification_type' => 'phone',
            'status' => 'not_required',
            'provider' => null,
            'verified_at' => null,
            'expires_at' => null,
            'rejection_reason' => null,
            'metadata_json' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }
}
