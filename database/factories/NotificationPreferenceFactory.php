<?php

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationPreference>
 */
class NotificationPreferenceFactory extends Factory
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
            'in_app_enabled' => true,
            'email_enabled' => true,
            'sms_future_enabled' => false,
            'push_future_enabled' => false,
            'urgent_always_in_app' => true,
            'critical_ignore_quiet_hours' => true,
            'quiet_hours_enabled' => false,
            'quiet_hours_start' => null,
            'quiet_hours_end' => null,
            'timezone' => 'UTC',
            'language_locale' => 'en',
            'digest_type' => 'none',
            'digest_time' => null,
        ];
    }
}
