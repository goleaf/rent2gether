<?php

namespace Database\Factories;

use App\Models\NotificationCategoryPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationCategoryPreference>
 */
class NotificationCategoryPreferenceFactory extends Factory
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
            'notification_category' => $this->faker->randomElement(['booking', 'payment', 'message', 'system']),
            'in_app_enabled' => true,
            'email_enabled' => true,
            'sms_future_enabled' => false,
            'push_future_enabled' => false,
            'digest_only' => false,
            'urgent_allowed' => true,
            'critical_allowed' => true,
        ];
    }
}
