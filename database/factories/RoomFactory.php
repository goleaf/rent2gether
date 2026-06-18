<?php

namespace Database\Factories;

use App\Enums\GenderType;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'title' => 'Room '.$this->faker->numberBetween(1, 20),
            'gender_type' => $this->faker->randomElement(GenderType::cases())->value,
            'description' => $this->faker->sentence(),
            'capacity' => $this->faker->numberBetween(2, 8),
            'area_sqm' => $this->faker->randomFloat(1, 10, 40),
            'has_lock' => $this->faker->boolean(),
            'has_window' => true,
            'has_wardrobe' => $this->faker->boolean(),
            'has_desk' => $this->faker->boolean(),
            'has_ac' => $this->faker->boolean(),
            'has_heating' => true,
            'has_balcony' => $this->faker->boolean(20),
            'status' => 'active',
        ];
    }
}
