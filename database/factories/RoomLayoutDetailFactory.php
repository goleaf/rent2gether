<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomLayoutDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomLayoutDetail>
 */
class RoomLayoutDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'area' => $this->faker->randomFloat(2, 10, 30),
            'length_meters' => $this->faker->randomFloat(2, 3, 7),
            'width_meters' => $this->faker->randomFloat(2, 3, 6),
            'ceiling_height_meters' => $this->faker->randomFloat(2, 2.4, 3.2),
            'windows_count' => $this->faker->numberBetween(1, 3),
            'window_size' => 'standard',
            'window_view' => 'courtyard',
            'windows_face_yard' => true,
            'windows_face_street' => false,
            'windows_face_quiet_side' => true,
            'windows_face_noisy_road' => false,
            'cardinal_direction' => $this->faker->randomElement(['north', 'east', 'south', 'west']),
            'has_balcony' => $this->faker->boolean(20),
            'balcony_accessible' => false,
            'has_free_passage_space' => true,
            'narrow_passages' => false,
            'has_many_free_space' => false,
            'has_little_free_space' => false,
        ];
    }
}
