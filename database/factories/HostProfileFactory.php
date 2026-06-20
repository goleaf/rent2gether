<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\HostProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostProfile>
 */
class HostProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'display_name' => $this->faker->firstName(),
            'host_display_name' => $this->faker->firstName(),
            'host_type' => 'individual',
            'avatar_path' => null,
            'about' => $this->faker->paragraph(),
            'about_host' => $this->faker->paragraph(),
            'languages_json' => ['en', 'ru'],
            'response_time_minutes' => 120,
            'response_rate' => 95,
            'acceptance_rate' => 90,
            'response_style' => 'friendly',
            'lives_in_property' => false,
            'lives_nearby' => true,
            'can_help_with_check_in' => true,
            'emergency_contact_available' => true,
            'hosting_experience' => 'new_host',
            'default_check_in_time' => '15:00',
            'default_check_out_time' => '11:00',
            'default_cancellation_policy' => 'flexible',
            'default_deposit_setting' => 'none',
            'default_house_rules' => 'Quiet hours after 22:00.',
            'successful_check_ins_count' => 0,
            'host_cancellations_count' => 0,
            'complaints_count' => 0,
            'verified_host' => true,
            'hosting_since' => now()->subYear()->toDateString(),
            'default_currency' => 'EUR',
            'default_language' => 'en',
            'public_phone_visible' => false,
            'public_email_visible' => false,
            'emergency_contact_name' => null,
            'emergency_contact_phone' => null,
            'representative_name' => null,
            'representative_contact' => null,
            'representative_visible_to_guest' => false,
            'rating_average' => 0,
            'reviews_count' => 0,
            'cancellations_count' => 0,
            'verified_at' => now(),
            'status' => UserStatus::Active->value,
        ];
    }
}
