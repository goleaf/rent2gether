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
            'summary' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'notes' => $this->faker->sentence(),
            'sleeping_arrangement' => $this->faker->sentence(),
            'privacy_notes' => $this->faker->sentence(),
        ];
    }
}
