<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'conversation_number' => 'CONV-'.now()->format('Y').'-'.Str::upper(Str::random(8)),
            'conversation_type' => 'booking',
            'status' => 'active',
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'guest_unread_count' => 0,
            'host_unread_count' => 0,
            'has_urgent_messages' => false,
            'has_important_messages' => false,
            'guest_can_write' => true,
            'host_can_write' => true,
            'is_read_only' => false,
            'is_system_only' => false,
            'last_message_at' => now(),
        ];
    }
}
