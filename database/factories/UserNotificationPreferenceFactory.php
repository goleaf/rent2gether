<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserNotificationPreference>
 */
class UserNotificationPreferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => 'bookings',
            'channel' => 'in_app',
            'enabled' => true,
            'urgent_allowed' => true,
            'quiet_hours_enabled' => false,
            'quiet_hours_from' => null,
            'quiet_hours_to' => null,
        ];
    }
}
