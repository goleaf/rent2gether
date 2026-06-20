<?php

namespace Database\Factories;

use App\Models\ListingReadinessCheck;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ListingReadinessCheck> */
class ListingReadinessCheckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->host(),
            'property_id' => Property::factory(),
            'room_id' => null,
            'sleeping_place_id' => null,
            'check_key' => 'property_title',
            'status' => 'missing',
            'required' => true,
            'message_key' => 'listing_readiness.messages.property_title',
            'message_params_json' => null,
        ];
    }
}
