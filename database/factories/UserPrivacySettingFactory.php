<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPrivacySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPrivacySetting>
 */
class UserPrivacySettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'show_real_name' => false,
            'show_avatar' => true,
            'show_age_range' => true,
            'show_gender' => false,
            'show_city' => true,
            'show_languages' => true,
            'show_rating' => true,
            'show_completed_stays_count' => true,
            'show_reviews_count' => true,
            'show_phone_after_booking' => true,
            'show_email_after_booking' => false,
            'show_identity_verified_badge' => true,
            'allow_host_to_see_guest_profile' => true,
            'allow_guest_to_see_host_contact_after_booking' => true,
        ];
    }
}
