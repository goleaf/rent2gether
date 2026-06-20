<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomTranslation>
 */
class RoomTranslationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'locale' => 'en',
            'title' => 'Shared room',
            'short_description' => 'A shared room with clear house rules.',
            'full_description' => 'A practical shared room for guests who are comfortable living near other people.',
            'summary' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'notes' => $this->faker->sentence(),
            'sleeping_arrangement' => $this->faker->sentence(),
            'privacy_notes' => $this->faker->sentence(),
            'room_description' => $this->faker->sentence(),
            'room_rules_text' => $this->faker->sentence(),
            'room_pros' => $this->faker->sentence(),
            'room_cons' => $this->faker->sentence(),
            'who_lives_nearby_text' => $this->faker->sentence(),
            'quiet_hours_text' => $this->faker->sentence(),
            'storage_instructions' => $this->faker->sentence(),
            'work_study_instructions' => $this->faker->sentence(),
            'food_rules_text' => $this->faker->sentence(),
            'conflict_instructions' => $this->faker->sentence(),
            'special_notes' => $this->faker->sentence(),
            'shared_space_instructions' => $this->faker->sentence(),
        ];
    }
}
