<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\FavoriteCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FavoriteCollection>
 */
class FavoriteCollectionFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->words(2, true);

        return [
            'user_id' => User::factory(),
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'description' => null,
            'icon' => 'heart',
            'color' => 'emerald',
            'type' => 'custom',
            'city_id' => null,
            'check_in_date' => null,
            'check_out_date' => null,
            'nights_count' => null,
            'guests_count' => 1,
            'budget_min' => null,
            'budget_max' => null,
            'currency' => 'EUR',
            'sort_order' => 0,
            'is_default' => false,
            'is_pinned' => false,
            'is_archived' => false,
        ];
    }

    public function default(string $type = 'cheap'): self
    {
        return $this->state(fn (): array => [
            'title' => __('favorites.default_collections.'.$type),
            'slug' => $type,
            'type' => $type,
            'is_default' => true,
        ]);
    }

    public function forCity(?City $city): self
    {
        return $this->state(fn (): array => [
            'city_id' => $city?->id,
        ]);
    }
}
