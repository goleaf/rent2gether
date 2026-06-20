<?php

namespace Database\Factories;

use App\Models\HostListingWizardSession;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostListingWizardSession>
 */
class HostListingWizardSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'current_step' => 'property',
            'completed_steps_json' => [],
            'skipped_steps_json' => [],
            'last_saved_at' => now(),
            'status' => 'draft',
        ];
    }
}
