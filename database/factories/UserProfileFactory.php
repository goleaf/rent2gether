<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'display_name' => $this->faker->firstName(),
            'avatar_path' => null,
            'date_of_birth' => $this->faker->dateTimeBetween('-55 years', '-18 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['female', 'male', 'not_specified']),
            'country_id' => Country::factory(),
            'city_id' => City::factory(),
            'phone' => $this->faker->phoneNumber(),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
            'about' => $this->faker->sentence(),
            'languages_json' => ['en', 'ru'],
            'occupation' => $this->faker->jobTitle(),
            'travel_purpose' => 'temporary_stay',
            'smokes' => false,
            'has_pets' => false,
            'allergies' => null,
            'prefers_quiet' => true,
            'sleep_schedule' => 'regular',
            'social_level' => 'balanced',
            'identity_verified_at' => now(),
            'rating_average' => 0,
            'reviews_count' => 0,
            'complaints_count' => 0,
            'status' => UserStatus::Active->value,
        ];
    }
}
