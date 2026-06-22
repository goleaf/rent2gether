<?php

namespace Database\Factories;

use App\Models\NotificationStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationStatusLog>
 */
class NotificationStatusLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_id' => null,
            'notification_delivery_id' => null,
            'notification_reminder_id' => null,
            'user_id' => User::factory(),
            'old_status' => null,
            'new_status' => 'created',
            'reason_key' => null,
            'note' => null,
            'context_json' => null,
        ];
    }
}
