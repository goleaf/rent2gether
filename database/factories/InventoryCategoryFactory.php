<?php

namespace Database\Factories;

use App\Models\InventoryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryCategory>
 */
class InventoryCategoryFactory extends Factory
{
    public function definition(): array
    {
        $key = fake()->unique()->slug(2);

        return [
            'category_key' => $key,
            'name_translation_key' => 'inventory.categories.'.$key,
            'description_translation_key' => 'inventory.category_descriptions.'.$key,
            'sort_order' => fake()->numberBetween(1, 50),
            'active' => true,
        ];
    }
}
