<?php

namespace Database\Factories;

use App\Models\NotificationSystemEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationSystemEvent>
 */
class NotificationSystemEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_key' => 'notification_created',
            'event_type' => 'system',
            'notification_id' => null,
            'notification_event_id' => null,
            'notification_delivery_id' => null,
            'notification_reminder_id' => null,
            'source_type' => null,
            'source_id' => null,
            'user_id' => User::factory(),
            'occurred_at' => now(),
            'context_json' => null,
        ];
    }
}
