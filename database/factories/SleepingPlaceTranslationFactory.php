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
            'short_description' => $this->faker->sentence(),
            'full_description' => $this->faker->paragraph(),
            'summary' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'special_conditions' => $this->faker->sentence(),
            'privacy_notes' => $this->faker->sentence(),
            'accessibility_notes' => $this->faker->sentence(),
            'main_pros' => $this->faker->sentence(),
            'important_cons' => $this->faker->sentence(),
            'special_notes' => $this->faker->sentence(),
            'what_is_included' => $this->faker->sentence(),
            'what_guest_should_bring' => $this->faker->sentence(),
            'storage_instructions' => $this->faker->sentence(),
            'safety_notes' => $this->faker->sentence(),
            'sleeping_place_title' => 'Comfortable sleeping place',
            'sleeping_place_description' => $this->faker->sentence(),
            'sleeping_place_pros' => $this->faker->sentence(),
            'sleeping_place_cons' => $this->faker->sentence(),
            'sleeping_place_special_notes' => $this->faker->sentence(),
            'what_is_included_for_place' => $this->faker->sentence(),
            'what_guest_should_bring_for_place' => $this->faker->sentence(),
        ];
    }
}
