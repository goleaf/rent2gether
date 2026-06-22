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
        $params = ['reference' => 'RTG-DEMO'];

        return [
            'id' => (string) Str::uuid(),
            'notification_number' => 'NTF-'.now()->format('Y').'-'.$this->faker->unique()->numberBetween(100000, 999999),
            'type' => 'action_needed',
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'user_id' => User::factory(),
            'recipient_user_id' => User::factory(),
            'recipient_type' => 'guest',
            'notification_category' => 'system',
            'notification_type' => 'info',
            'priority' => 'normal',
            'data' => ['params' => ['reference' => 'RTG-DEMO']],
            'title_key' => 'notifications.action_needed.title',
            'body_key' => 'notifications.action_needed.body',
            'title_translation_key' => null,
            'body_translation_key' => null,
            'translation_params_json' => $params,
            'locale' => 'en',
            'action_url' => null,
            'channel' => 'database',
            'status' => 'unread',
            'read_at' => null,
            'is_read' => false,
            'is_dismissed' => false,
            'is_action_required' => false,
            'is_urgent' => false,
            'is_critical' => false,
        ];
    }
}
