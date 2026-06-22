<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationStatusLog>
 */
class ConversationStatusLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'user_id' => User::factory(),
            'old_status' => 'active',
            'new_status' => 'closed',
            'reason_key' => 'manual',
            'context_json' => [],
        ];
    }
}
