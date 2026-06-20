<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyAccessDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyAccessDetail>
 */
class PropertyAccessDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'entrance_type' => $this->faker->randomElement(['shared_entrance', 'private_entrance', 'through_yard']),
            'has_private_entrance' => $this->faker->boolean(25),
            'has_shared_entrance' => true,
            'entrance_through_yard' => $this->faker->boolean(35),
            'entrance_through_reception' => $this->faker->boolean(10),
            'has_intercom' => $this->faker->boolean(80),
            'has_intercom_code' => $this->faker->boolean(60),
            'has_door_code' => $this->faker->boolean(50),
            'has_gate_code' => $this->faker->boolean(20),
            'has_key' => true,
            'has_keycard' => $this->faker->boolean(15),
            'has_electronic_lock' => $this->faker->boolean(25),
            'has_key_safe' => $this->faker->boolean(35),
            'key_safe_location_note' => $this->faker->optional()->sentence(),
            'code_visible_after_confirmation' => true,
            'code_visible_after_payment' => true,
            'code_visible_on_checkin_day' => false,
            'code_changes_after_guest' => $this->faker->boolean(50),
            'key_sets_count' => $this->faker->numberBetween(1, 4),
            'key_pickup_method' => $this->faker->randomElement(['self_check_in', 'meet_host', 'meet_host_representative']),
            'key_pickup_contact_type' => $this->faker->randomElement(['host', 'host_representative']),
            'meet_host_required' => $this->faker->boolean(35),
            'meet_host_representative_required' => $this->faker->boolean(15),
            'self_check_in_available' => $this->faker->boolean(60),
            'self_check_in_available_at_night' => $this->faker->boolean(45),
            'check_in_instruction_available' => true,
            'entrance_photo_available' => $this->faker->boolean(65),
            'door_photo_available' => $this->faker->boolean(55),
            'key_safe_photo_available' => $this->faker->boolean(35),
            'emergency_contact_available' => true,
            'what_if_code_fails' => $this->faker->sentence(),
            'what_if_key_does_not_work' => $this->faker->sentence(),
            'access_24_7' => $this->faker->boolean(70),
            'can_return_at_night' => $this->faker->boolean(80),
            'has_night_entry_restrictions' => $this->faker->boolean(20),
            'night_entry_restriction_text' => $this->faker->optional()->sentence(),
            'must_be_quiet_at_night_entry' => true,
            'guest_visitors_allowed' => $this->faker->boolean(35),
            'guest_visitors_need_approval' => $this->faker->boolean(50),
            'courier_rules_enabled' => $this->faker->boolean(50),
            'delivery_allowed' => $this->faker->boolean(70),
            'delivery_dropoff_location' => $this->faker->optional()->randomElement(['building entrance', 'apartment door', 'reception']),
            'courier_can_enter_building' => $this->faker->boolean(40),
            'courier_can_come_to_door' => $this->faker->boolean(35),
            'courier_must_leave_at_entrance' => $this->faker->boolean(50),
            'parcels_allowed' => $this->faker->boolean(45),
            'parcel_pickup_location' => $this->faker->optional()->sentence(3),
            'delivery_responsibility_note' => $this->faker->optional()->sentence(),
        ];
    }
}
