<?php

namespace Database\Factories;

use App\Models\HostHintSnapshot;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostHintSnapshot>
 */
class HostHintSnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => null,
            'room_id' => null,
            'sleeping_place_id' => null,
            'hint_key' => 'add_main_sleeping_place_photo',
            'category' => 'photos',
            'type' => 'suggestion',
            'importance' => 'medium',
            'priority' => 50,
            'message_key' => 'host_hints.messages.add_main_sleeping_place_photo',
            'message_params_json' => [],
            'action_key' => 'add_photo',
            'action_url' => null,
            'status' => 'active',
            'source' => 'factory',
            'show_in_wizard' => true,
            'show_in_dashboard' => true,
            'show_before_publish' => false,
            'show_on_listing_card' => true,
            'calculated_at' => now(),
            'expires_at' => null,
        ];
    }

    public function forTarget(User $host, ?Property $property = null, ?Room $room = null, ?SleepingPlace $place = null): self
    {
        return $this->state(fn (): array => [
            'user_id' => $host->id,
            'property_id' => $property?->id,
            'room_id' => $room?->id,
            'sleeping_place_id' => $place?->id,
        ]);
    }
}
