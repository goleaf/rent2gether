<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationInternalNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationInternalNote>
 */
class ConversationInternalNoteFactory extends Factory
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
            'host_user_id' => User::factory(),
            'note' => fake()->sentence(),
            'note_type' => 'other',
            'created_by_user_id' => User::factory(),
            'visible_to_host' => true,
            'visible_to_guest' => false,
            'internal' => true,
        ];
    }
}
