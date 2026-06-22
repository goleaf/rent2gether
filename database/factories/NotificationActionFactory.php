<?php

namespace Database\Factories;

use App\Models\NotificationAction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationAction>
 */
class NotificationActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_id' => Notification::factory(),
            'user_id' => User::factory(),
            'action_type' => 'open_booking',
            'status' => 'available',
            'source_type' => 'booking',
            'source_id' => null,
            'performed_at' => null,
            'result_message_key' => null,
            'result_context_json' => null,
        ];
    }
}
