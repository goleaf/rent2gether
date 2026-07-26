<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\NotificationDeliveryAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDeliveryAttempt>
 */
class NotificationDeliveryAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_delivery_id' => NotificationDelivery::factory(),
            'notification_id' => Notification::factory(),
            'channel' => 'email',
            'attempt_number' => 1,
            'status' => 'ready',
            'attempted_at' => now(),
            'provider' => null,
            'provider_response_json' => null,
            'failure_reason' => null,
        ];
    }
}
