<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'thread_id' => MessageThread::factory(),
            'sender_id' => User::factory(),
            'body' => $this->faker->sentence(),
            'attachment' => null,
            'attachment_type' => null,
            'attachments_json' => [],
            'is_system_message' => false,
            'is_important' => false,
            'read_at' => null,
        ];
    }
}
