<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationMessageAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationMessageAttachment>
 */
class ConversationMessageAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_message_id' => ConversationMessage::factory(),
            'conversation_id' => Conversation::factory(),
            'uploaded_by_user_id' => User::factory(),
            'attachment_type' => 'photo',
            'media_type' => 'photo',
            'path' => 'messages/demo.jpg',
            'thumbnail_path' => 'messages/demo-thumb.jpg',
            'caption' => fake()->sentence(),
            'visibility' => 'guest_and_host',
        ];
    }
}
