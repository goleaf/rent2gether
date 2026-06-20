<?php

namespace Database\Factories;

use App\Models\RoomTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoomTemplate> */
class RoomTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->host(),
            'name' => 'Shared room template',
            'room_type' => 'shared_room',
            'template_json' => ['room_type' => 'shared_room', 'gender_policy' => 'mixed'],
            'is_default' => false,
        ];
    }
}
