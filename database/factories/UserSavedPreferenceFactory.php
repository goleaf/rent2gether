<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSavedPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSavedPreference>
 */
class UserSavedPreferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'preferred_currency' => 'EUR',
            'preferred_locale' => 'en',
            'preferred_timezone' => 'Europe/Vilnius',
            'distance_unit' => 'km',
            'price_display_mode' => 'both',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'mobile_compact_mode' => true,
            'show_total_price_with_deposit' => true,
            'show_total_price_without_deposit' => true,
        ];
    }
}
