<?php

namespace Database\Factories;

use App\Models\NotificationDeviceToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NotificationDeviceToken>
 */
class NotificationDeviceTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'platform' => $this->faker->randomElement(['web_future', 'ios_future', 'android_future']),
            'device_name' => $this->faker->optional()->words(2, true),
            'token_hash' => hash('sha256', Str::random(40)),
            'token_encrypted' => null,
            'active' => true,
            'last_used_at' => now()->subMinutes($this->faker->numberBetween(1, 500)),
        ];
    }
}
