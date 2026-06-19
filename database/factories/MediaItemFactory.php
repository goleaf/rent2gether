<?php

namespace Database\Factories;

use App\Models\MediaItem;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaItem>
 */
class MediaItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mediable_type' => Property::class,
            'mediable_id' => Property::factory(),
            'owner_user_id' => User::factory(),
            'collection' => 'gallery',
            'disk' => 'public',
            'path' => 'properties/demo.jpg',
            'thumbnail_path' => 'properties/demo-thumb.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 120_000,
            'width' => 1200,
            'height' => 800,
            'alt_text' => null,
            'sort_order' => 0,
            'is_cover' => true,
            'status' => 'active',
        ];
    }
}
