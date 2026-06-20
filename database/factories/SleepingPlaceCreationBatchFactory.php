<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlaceCreationBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SleepingPlaceCreationBatch> */
class SleepingPlaceCreationBatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->host(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'batch_name' => null,
            'places_count' => 1,
            'template_json' => ['place_type' => 'single_bed'],
            'status' => 'draft',
        ];
    }
}
