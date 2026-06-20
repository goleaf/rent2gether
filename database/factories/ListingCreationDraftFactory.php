<?php

namespace Database\Factories;

use App\Models\ListingCreationDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ListingCreationDraft> */
class ListingCreationDraftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->host(),
            'draft_type' => 'full_listing_wizard',
            'property_id' => null,
            'room_id' => null,
            'sleeping_place_id' => null,
            'current_step' => 'property',
            'draft_data_json' => [],
            'completed_steps_json' => [],
            'last_saved_at' => now(),
        ];
    }
}
