<?php

namespace Database\Factories;

use App\Models\BookingStay;
use App\Models\StayVisibilityPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StayVisibilityPreference>
 */
class StayVisibilityPreferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_stay_id' => BookingStay::factory(),
            'user_id' => User::factory(),
            'show_public_name' => true,
            'show_age_range' => true,
            'show_gender_if_room_policy_relevant' => true,
            'show_city' => true,
            'show_languages' => true,
            'show_stay_purpose' => true,
            'show_sleep_schedule' => false,
            'show_smoking_status' => false,
            'show_sociability_level' => false,
            'show_checkout_date' => true,
        ];
    }
}
