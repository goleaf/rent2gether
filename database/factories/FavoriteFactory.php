<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Favorite>
 */
class FavoriteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bed_id' => Bed::factory(),
            'price_at_save' => $this->faker->randomFloat(2, 10, 50),
            'notify_available' => false,
            'notify_price_drop' => false,
        ];
    }
}
