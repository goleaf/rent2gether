<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserLanguage>
 */
class UserLanguageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'language_code' => 'en',
            'level' => 'intermediate',
            'is_primary' => true,
        ];
    }
}
