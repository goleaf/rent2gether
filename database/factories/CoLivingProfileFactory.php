<?php

namespace Database\Factories;

use App\Models\CoLivingProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoLivingProfile>
 */
class CoLivingProfileFactory extends Factory
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
            'public_alias' => $this->faker->firstName(),
            'age_range' => $this->faker->randomElement(['18-24', '25-34', '35-44', '45-54']),
            'gender_for_room_policy' => $this->faker->randomElement(['female', 'male', 'not_specified']),
            'country_id' => null,
            'city_id' => null,
            'languages_json' => ['en'],
            'stay_purpose' => $this->faker->randomElement(['tourism', 'work', 'study', 'temporary_housing']),
            'guest_type' => $this->faker->randomElement(['tourist', 'student', 'working', 'remote_worker', 'short_term_guest']),
            'tourist' => false,
            'student' => false,
            'working' => true,
            'remote_worker' => false,
            'long_term_guest' => false,
            'short_term_guest' => true,
            'sleep_schedule' => $this->faker->randomElement(['early_bird', 'normal', 'night_owl']),
            'wake_schedule' => $this->faker->randomElement(['early', 'normal', 'late']),
            'home_presence_level' => $this->faker->randomElement(['often_home', 'balanced', 'rarely_home']),
            'smokes' => false,
            'smoking_location' => null,
            'has_pet' => false,
            'social_level' => $this->faker->randomElement(['quiet', 'calm', 'social']),
            'prefers_quiet' => true,
            'cleanliness_level' => $this->faker->randomElement(['basic', 'tidy', 'very_tidy']),
            'participates_in_cleaning' => true,
            'respects_personal_space' => true,
            'roommate_rating_average' => $this->faker->randomFloat(2, 4.0, 5.0),
            'roommate_reviews_count' => $this->faker->numberBetween(0, 20),
            'roommate_complaints_count' => 0,
            'profile_completed_at' => now(),
        ];
    }
}
