<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationSafetyWarning;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationSafetyWarning>
 */
class ConversationSafetyWarningFactory extends Factory
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
            'warning_key' => 'possible_off_platform_payment',
            'severity' => 'warning',
            'triggered_by_user_id' => User::factory(),
            'visible_to_sender' => true,
            'visible_to_recipient' => false,
            'message_key' => 'messages.messages.off_platform_payment_warning',
            'context_json' => [],
        ];
    }
}
