<?php

namespace Database\Factories;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'city' => $this->faker->city(),
            'price_max' => $this->faker->randomFloat(2, 15, 50),
            'notify_new_places' => false,
            'is_active' => true,
        ];
    }
}
