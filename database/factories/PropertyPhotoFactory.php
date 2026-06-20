<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropertyPhoto> */
class PropertyPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'uploaded_by_user_id' => User::factory()->host(),
            'path' => 'properties/photo.jpg',
            'thumbnail_path' => null,
            'caption' => null,
            'sort_order' => 0,
            'is_primary' => true,
            'is_main' => true,
            'status' => 'active',
            'visibility' => 'public',
        ];
    }
}
