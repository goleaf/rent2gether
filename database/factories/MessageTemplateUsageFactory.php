<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\MessageTemplate;
use App\Models\MessageTemplateUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageTemplateUsage>
 */
class MessageTemplateUsageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_template_id' => MessageTemplate::factory(),
            'template_key' => 'i_will_arrive_soon',
            'conversation_id' => Conversation::factory(),
            'conversation_message_id' => ConversationMessage::factory(),
            'user_id' => User::factory(),
            'used_at' => now(),
        ];
    }
}
