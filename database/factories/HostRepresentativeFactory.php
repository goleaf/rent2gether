<?php

namespace Database\Factories;

use App\Models\HostRepresentative;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostRepresentative>
 */
class HostRepresentativeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'host_user_id' => User::factory()->host(),
            'representative_user_id' => null,
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => null,
            'role_description' => 'check_in_contact',
            'can_help_with_check_in' => true,
            'can_help_with_keys' => true,
            'can_help_with_cleaning_coordination' => false,
            'can_be_contacted_by_guest' => true,
            'visible_after_booking_only' => true,
            'active' => true,
        ];
    }
}
