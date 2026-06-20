<?php

namespace Database\Factories;

use App\Models\HostHintDismissal;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostHintDismissal>
 */
class HostHintDismissalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => null,
            'room_id' => null,
            'sleeping_place_id' => null,
            'hint_key' => 'add_main_sleeping_place_photo',
            'context' => 'dashboard',
            'dismissed_until' => now()->addWeek(),
            'dismissed_at' => now(),
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
