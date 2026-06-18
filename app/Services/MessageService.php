<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessageService
{
    public function getOrCreateConversation(User $userA, User $userB, ?int $bookingId = null, ?int $bedId = null): Conversation
    {
        $ids = [min($userA->id, $userB->id), max($userA->id, $userB->id)];

        return Conversation::firstOrCreate([
            'participant_one_id' => $ids[0],
            'participant_two_id' => $ids[1],
            'booking_id' => $bookingId,
        ], [
            'bed_id' => $bedId,
            'last_message_at' => now(),
        ]);
    }

    public function send(Conversation $conversation, User $sender, string $body, bool $isImportant = false): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => $body,
            'is_important' => $isImportant,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $message;
    }

    public function sendSystemMessage(Conversation $conversation, string $body): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->participant_one_id,
            'body' => $body,
            'is_system_message' => true,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $message;
    }

    public function markConversationRead(Conversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
