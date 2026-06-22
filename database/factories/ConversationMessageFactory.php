<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ConversationMessage>
 */
class ConversationMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_number' => 'MSG-'.now()->format('Y').'-'.Str::upper(Str::random(8)),
            'conversation_id' => Conversation::factory(),
            'sender_user_id' => User::factory(),
            'sender_type' => 'guest',
            'recipient_user_id' => User::factory(),
            'recipient_type' => 'host',
            'message_type' => 'text',
            'status' => 'sent',
            'body' => fake()->sentence(),
            'is_system' => false,
            'is_important' => false,
            'is_urgent' => false,
            'is_pinned' => false,
            'is_internal_note' => false,
            'original_locale' => 'en',
            'sent_at' => now(),
        ];
    }
}
