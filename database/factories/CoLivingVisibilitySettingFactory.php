<?php

namespace Database\Factories;

use App\Models\CoLivingVisibilitySetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoLivingVisibilitySetting>
 */
class CoLivingVisibilitySettingFactory extends Factory
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
            'show_public_alias' => true,
            'show_real_first_name' => false,
            'show_avatar' => false,
            'show_age_range' => true,
            'show_gender_if_room_policy' => true,
            'show_country' => false,
            'show_city' => false,
            'show_languages' => true,
            'show_stay_purpose' => true,
            'show_guest_type' => true,
            'show_sleep_schedule' => true,
            'show_wake_schedule' => false,
            'show_home_presence' => true,
            'show_smoking_status' => true,
            'show_pet_status' => false,
            'show_social_level' => true,
            'show_quiet_preference' => true,
            'show_cleanliness_level' => false,
            'show_roommate_rating' => true,
            'show_checkout_date_to_future_roommates' => true,
            'allow_profile_in_prebooking_summary' => true,
            'allow_profile_after_confirmed_booking' => true,
        ];
    }
}
