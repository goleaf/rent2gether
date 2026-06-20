<?php

namespace Database\Factories;

use App\Models\SleepingPlaceTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SleepingPlaceTemplate> */
class SleepingPlaceTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->host(),
            'name' => 'Single bed template',
            'place_type' => 'single_bed',
            'template_json' => ['place_type' => 'single_bed', 'base_price' => 20],
            'is_default' => false,
        ];
    }
}
