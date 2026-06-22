<?php

namespace Database\Factories;

use App\Models\NotificationDigest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDigest>
 */
class NotificationDigestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'digest_number' => 'NDG-'.now()->format('Y').'-'.$this->faker->unique()->numberBetween(100000, 999999),
            'user_id' => User::factory(),
            'digest_type' => 'daily',
            'status' => 'created',
            'period_start' => now()->subDay(),
            'period_end' => now(),
            'notification_count' => 0,
            'urgent_count' => 0,
            'important_count' => 0,
            'title_translation_key' => 'notifications.digest.title',
            'body_translation_key' => 'notifications.digest.body',
            'translation_params_json' => ['count' => 0],
            'sent_at' => null,
            'read_at' => null,
            'closed_at' => null,
        ];
    }
}
