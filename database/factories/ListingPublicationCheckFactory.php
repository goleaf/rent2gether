<?php

namespace Database\Factories;

use App\Models\ListingPublicationCheck;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingPublicationCheck>
 */
class ListingPublicationCheckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => null,
            'sleeping_place_id' => null,
            'check_key' => 'missing_price',
            'category' => 'sleeping_places',
            'severity' => 'critical',
            'status' => 'open',
            'message_key' => 'listing_wizard.checks.missing_price',
            'message_params_json' => [],
            'is_required' => true,
            'is_blocking' => true,
            'fixed_at' => null,
        ];
    }
}
