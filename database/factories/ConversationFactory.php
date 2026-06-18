<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'participant_one_id' => User::factory(),
            'participant_two_id' => User::factory(),
            'last_message_at' => now(),
        ];
    }
}
