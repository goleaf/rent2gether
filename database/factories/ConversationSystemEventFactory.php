<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationSystemEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationSystemEvent>
 */
class ConversationSystemEventFactory extends Factory
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
            'conversation_message_id' => ConversationMessage::factory(),
            'event_key' => 'booking_created',
            'event_type' => 'system',
            'translation_key' => 'messages.system_events.booking_created',
            'importance_level' => 'normal',
            'occurred_at' => now(),
        ];
    }
}
