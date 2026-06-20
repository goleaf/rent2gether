<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoomPhoto> */
class RoomPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'uploaded_by_user_id' => User::factory()->host(),
            'path' => 'rooms/photo.jpg',
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
