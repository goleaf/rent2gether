<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlacePhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SleepingPlacePhoto> */
class SleepingPlacePhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'uploaded_by_user_id' => User::factory()->host(),
            'path' => 'sleeping-places/photo.jpg',
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
