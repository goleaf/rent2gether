<?php

namespace Database\Factories;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(PropertyType::cases())->value,
            'description' => $this->faker->paragraph(),
            'country' => 'Germany',
            'city' => $this->faker->city(),
            'district' => $this->faker->word(),
            'street' => $this->faker->streetName(),
            'building' => $this->faker->buildingNumber(),
            'floor' => $this->faker->numberBetween(1, 10),
            'has_elevator' => $this->faker->boolean(),
            'amenities' => ['wifi', 'kitchen', 'washer'],
            'rules' => ['no_smoking', 'no_parties'],
            'status' => PropertyStatus::Active->value,
        ];
    }
}
