<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDelivery>
 */
class NotificationDeliveryFactory extends Factory
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
            'recipient_user_id' => User::factory(),
            'channel' => $this->faker->randomElement(['in_app', 'email']),
            'status' => 'ready',
            'scheduled_at' => now(),
            'sent_at' => null,
            'delivered_at' => null,
            'failed_at' => null,
            'read_at' => null,
            'provider' => null,
            'provider_message_id' => null,
            'provider_response_json' => null,
            'failure_reason' => null,
            'attempt_count' => 0,
            'next_retry_at' => null,
        ];
    }
}
