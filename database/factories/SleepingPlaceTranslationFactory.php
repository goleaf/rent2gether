<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceTranslation>
 */
class SleepingPlaceTranslationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'locale' => 'en',
            'title' => 'Comfortable sleeping place',
            'summary' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'privacy_notes' => $this->faker->sentence(),
            'accessibility_notes' => $this->faker->sentence(),
        ];
    }
}
