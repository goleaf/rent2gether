<?php

namespace Database\Factories;

use App\Models\HostListingSuggestion;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HostListingSuggestion> */
class HostListingSuggestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->host(),
            'property_id' => Property::factory(),
            'room_id' => null,
            'sleeping_place_id' => null,
            'suggestion_key' => 'add_sleeping_place_photo',
            'severity' => 'info',
            'message_key' => 'listing_readiness.suggestions.add_sleeping_place_photo',
            'action_key' => 'add_photo',
            'status' => 'active',
        ];
    }
}
