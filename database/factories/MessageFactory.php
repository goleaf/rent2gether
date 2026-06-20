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
            'sender_user_id' => null,
            'recipient_user_id' => null,
            'booking_id' => null,
            'property_id' => null,
            'sleeping_place_id' => null,
            'body' => $this->faker->sentence(),
            'attachment' => null,
            'attachment_type' => null,
            'attachments' => [],
            'attachments_json' => [],
            'is_system_message' => false,
            'is_important' => false,
            'important' => false,
            'system_message' => false,
            'locale' => 'en',
            'read_at' => null,
        ];
    }
}
