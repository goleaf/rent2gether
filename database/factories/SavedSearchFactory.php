<?php

namespace Database\Factories;

use App\Models\City;
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
            'city_id' => City::factory(),
            'locale' => 'en',
            'name' => 'My search',
            'city' => $this->faker->city(),
            'district' => null,
            'check_in' => now()->addWeek()->toDateString(),
            'check_out' => now()->addDays(10)->toDateString(),
            'flexible_dates' => false,
            'nights' => 3,
            'price_min' => 10,
            'price_max' => 50,
            'currency' => 'EUR',
            'room_type' => 'shared',
            'bed_type' => 'single',
            'amenities' => ['wifi'],
            'filters' => ['quiet' => true],
            'filters_json' => ['quiet' => true],
            'notify_new_places' => true,
            'notify_price_drop' => false,
            'notify_available' => true,
            'notify_frequency' => 'daily',
            'is_active' => true,
        ];
    }
}
