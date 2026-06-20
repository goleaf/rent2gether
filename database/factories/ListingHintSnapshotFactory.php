<?php

namespace Database\Factories;

use App\Models\ListingHintSnapshot;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingHintSnapshot>
 */
class ListingHintSnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'city_id' => null,
            'hint_key' => 'new_listing',
            'category' => 'trust',
            'type' => 'info',
            'importance' => 'low',
            'priority' => 10,
            'message_key' => 'guest_hints.messages.new_listing',
            'message_params_json' => [],
            'source' => 'factory',
            'show_on_card' => false,
            'show_on_detail' => true,
            'show_before_booking' => false,
            'show_in_favorites' => false,
            'show_in_saved_search' => false,
            'valid_from' => null,
            'valid_until' => null,
            'calculated_at' => now(),
            'expires_at' => now()->addDay(),
        ];
    }

    public function forPlace(SleepingPlace $place): self
    {
        return $this->state(fn (): array => [
            'sleeping_place_id' => $place->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'city_id' => $place->property?->city_id,
        ]);
    }
}
