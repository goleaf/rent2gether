<?php

namespace Database\Factories;

use App\Models\MediaItem;
use App\Models\MediaItemTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaItemTranslation>
 */
class MediaItemTranslationFactory extends Factory
{
    protected $model = MediaItemTranslation::class;

    public function definition(): array
    {
        return [
            'media_item_id' => MediaItem::factory(),
            'locale' => 'en',
            'caption' => fake()->sentence(4),
        ];
    }
}
