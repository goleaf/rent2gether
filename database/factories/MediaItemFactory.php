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
            'owner_type' => Property::class,
            'owner_id' => Property::factory(),
            'mediable_type' => Property::class,
            'mediable_id' => Property::factory(),
            'owner_user_id' => User::factory(),
            'collection' => 'gallery',
            'disk' => 'public',
            'path' => 'properties/demo.jpg',
            'thumbnail_path' => 'properties/demo-thumb.jpg',
            'thumb_path' => 'properties/demo-thumb.jpg',
            'mobile_path' => 'properties/demo-mobile.jpg',
            'full_path' => 'properties/demo-full.jpg',
            'original_filename' => 'demo.jpg',
            'mime' => 'image/jpeg',
            'mime_type' => 'image/jpeg',
            'size' => 120_000,
            'size_bytes' => 120_000,
            'width' => 1200,
            'height' => 800,
            'alt_text' => null,
            'sort_order' => 0,
            'is_primary' => true,
            'is_cover' => true,
            'status' => 'active',
        ];
    }
}
