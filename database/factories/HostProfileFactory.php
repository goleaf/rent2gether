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
            'avatar_path' => null,
            'about' => $this->faker->paragraph(),
            'languages_json' => ['en', 'ru'],
            'response_time_minutes' => 120,
            'response_rate' => 95,
            'lives_in_property' => false,
            'lives_nearby' => true,
            'rating_average' => 0,
            'reviews_count' => 0,
            'cancellations_count' => 0,
            'verified_at' => now(),
            'status' => UserStatus::Active->value,
        ];
    }
}
