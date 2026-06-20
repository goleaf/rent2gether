<?php

namespace Database\Factories;

use App\Models\HostHintAction;
use App\Models\HostHintSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostHintAction>
 */
class HostHintActionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'host_hint_snapshot_id' => HostHintSnapshot::factory(),
            'action' => 'completed',
            'action_status' => 'done',
            'acted_at' => now(),
        ];
    }
}
