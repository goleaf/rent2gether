<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyTranslation>
 */
class PropertyTranslationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'locale' => 'en',
            'title' => $this->faker->sentence(3),
            'short_description' => $this->faker->sentence(),
            'summary' => $this->faker->sentence(),
            'full_description' => $this->faker->paragraph(),
            'description' => $this->faker->paragraph(),
            'location_description' => $this->faker->sentence(),
            'transport_description' => $this->faker->sentence(),
            'neighborhood_description' => $this->faker->sentence(),
            'parking_description' => $this->faker->sentence(),
            'condition_description' => $this->faker->sentence(),
            'access_description' => $this->faker->sentence(),
            'self_check_in_instructions' => $this->faker->sentence(),
            'getting_there' => $this->faker->sentence(),
            'what_guests_like' => $this->faker->sentence(),
            'what_to_know' => $this->faker->sentence(),
            'why_convenient' => $this->faker->sentence(),
            'suitable_for' => $this->faker->sentence(),
            'not_suitable_for' => $this->faker->sentence(),
            'main_pros' => $this->faker->sentence(),
            'important_cons' => $this->faker->sentence(),
            'what_to_know_beforehand' => $this->faker->sentence(),
            'what_is_included' => $this->faker->sentence(),
            'what_is_not_included' => $this->faker->sentence(),
            'what_to_bring' => $this->faker->sentence(),
            'where_to_store_belongings' => $this->faker->sentence(),
            'where_to_store_food' => $this->faker->sentence(),
            'kitchen_instructions' => $this->faker->sentence(),
            'bathroom_instructions' => $this->faker->sentence(),
            'laundry_instructions' => $this->faker->sentence(),
            'key_pickup_instructions' => $this->faker->sentence(),
            'night_entry_instructions' => $this->faker->sentence(),
            'delivery_instructions' => $this->faker->sentence(),
            'guest_visitor_rules_text' => $this->faker->sentence(),
            'courier_rules_text' => $this->faker->sentence(),
            'important_notes' => $this->faker->sentence(),
            'host_contact_instructions' => $this->faker->sentence(),
            'problem_instructions' => $this->faker->sentence(),
            'lost_key_instructions' => $this->faker->sentence(),
            'neighbor_conflict_instructions' => $this->faker->sentence(),
            'repair_problem_instructions' => $this->faker->sentence(),
            'check_in_instructions' => $this->faker->sentence(),
            'check_out_instructions' => $this->faker->sentence(),
            'house_rules_text' => $this->faker->sentence(),
            'safety_notes' => $this->faker->sentence(),
        ];
    }
}
