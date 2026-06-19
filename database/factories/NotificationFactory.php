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
            'type' => 'booking_update',
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'user_id' => User::factory(),
            'data' => ['booking_reference' => 'RTG-DEMO'],
            'title_key' => 'notifications.booking_update.title',
            'body_key' => 'notifications.booking_update.body',
            'action_url' => null,
            'channel' => 'database',
            'status' => 'unread',
            'read_at' => null,
        ];
    }
}
