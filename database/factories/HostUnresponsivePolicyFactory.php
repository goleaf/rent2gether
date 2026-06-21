<?php

namespace Database\Factories;

use App\Models\HostUnresponsivePolicy;
use App\Models\Property;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostUnresponsivePolicy>
 */
class HostUnresponsivePolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'property_id' => Property::factory(),
            'pre_check_in_response_minutes' => 240,
            'check_in_response_minutes' => 60,
            'guest_waiting_outside_response_minutes' => 20,
            'night_entry_response_minutes' => 15,
            'urgent_response_minutes' => 10,
            'notify_representative_if_available' => true,
            'auto_show_instructions_if_allowed' => true,
            'auto_block_no_show_while_active' => true,
            'allow_guest_cancellation_after_deadline' => true,
            'allow_guest_relocation_after_deadline' => true,
            'guest_friendly_refund_if_confirmed' => true,
            'active' => true,
        ];
    }
}
