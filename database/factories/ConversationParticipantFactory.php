<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationParticipant>
 */
class ConversationParticipantFactory extends Factory
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
            'participant_type' => 'guest',
            'display_name_snapshot' => fake()->name(),
            'can_write' => true,
            'can_read' => true,
            'can_upload' => true,
            'can_use_templates' => true,
            'muted' => false,
            'archived' => false,
            'joined_at' => now(),
        ];
    }
}
