<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => 'action_needed',
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'user_id' => User::factory(),
            'data' => ['params' => ['reference' => 'RTG-DEMO']],
            'title_key' => 'notifications.action_needed.title',
            'body_key' => 'notifications.action_needed.body',
            'action_url' => null,
            'channel' => 'database',
            'status' => 'unread',
            'read_at' => null,
        ];
    }
}
