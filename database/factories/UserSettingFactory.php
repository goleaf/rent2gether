<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSetting;
use App\Services\Privacy\PrivacyPreferences;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSetting>
 */
class UserSettingFactory extends Factory
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
            'locale' => fake()->randomElement(['en', 'ru']),
            'currency' => fake()->randomElement(['EUR', 'USD']),
            'active_mode' => UserSetting::MODE_GUEST,
            'account_role' => UserSetting::ROLE_GUEST,
            'notification_preferences_json' => [
                'email_messages' => true,
                'email_bookings' => true,
            ],
            'privacy_preferences_json' => PrivacyPreferences::defaults(),
        ];
    }
}
