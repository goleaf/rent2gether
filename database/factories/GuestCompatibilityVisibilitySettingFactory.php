<?php

namespace Database\Factories;

use App\Models\GuestCompatibilityVisibilitySetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestCompatibilityVisibilitySetting>
 */
class GuestCompatibilityVisibilitySettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'show_smoking_preference' => false,
            'show_sleep_schedule' => true,
            'show_work_study_status' => true,
            'show_home_presence' => false,
            'show_social_level' => true,
            'show_cleanliness_preference' => false,
            'show_room_preferences' => true,
            'show_workspace_needs' => true,
            'show_pet_preference' => false,
            'allow_use_for_matching' => true,
            'allow_show_to_hosts' => false,
            'allow_show_to_future_roommates' => false,
        ];
    }
}
