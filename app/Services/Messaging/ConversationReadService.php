<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationMessageRead;
use App\Models\User;

class ConversationReadService
{
    public function markConversationRead(User $user, Conversation $conversation): void
    {
        $conversation->conversationMessages()
            ->where('sender_user_id', '!=', $user->id)
            ->where('is_internal_note', false)
            ->whereNull('read_at')
            ->get()
            ->each(fn (ConversationMessage $message): ConversationMessageRead => $this->markMessageRead($user, $message));

        $this->syncUnreadCounters($conversation->refresh());
    }

    public function markMessageRead(User $user, ConversationMessage $message): ConversationMessageRead
    {
        $read = ConversationMessageRead::query()->firstOrCreate([
            'conversation_id' => $message->conversation_id,
            'conversation_message_id' => $message->id,
            'user_id' => $user->id,
        ], [
            'read_at' => now(),
        ]);

        $message->forceFill([
            'read_at' => $message->read_at ?: $read->read_at,
            'status' => 'read',
        ])->save();

        $participantType = (int) $message->conversation->guest_user_id === (int) $user->id ? 'guest' : 'host';
        $message->conversation->participants()
            ->where('user_id', $user->id)
            ->where('participant_type', $participantType)
            ->update([
                'last_read_message_id' => $message->id,
                'last_read_at' => $read->read_at,
            ]);

        $this->syncUnreadCounters($message->conversation->refresh());
        app(ConversationEventService::class)->record($message->conversation, 'message_read', [
            'user_id' => $user->id,
            'message_id' => $message->id,
        ]);

        return $read->refresh();
    }

    public function getUnreadCount(User $user): int
    {
        return Conversation::query()
            ->where(function ($query) use ($user): void {
                $query->where('guest_user_id', $user->id)
                    ->orWhere('host_user_id', $user->id);
            })
            ->get()
            ->sum(fn (Conversation $conversation): int => $this->getUnreadCountForConversation($user, $conversation));
    }

    public function getUnreadCountForConversation(User $user, Conversation $conversation): int
    {
        if ((int) $conversation->guest_user_id === (int) $user->id) {
            return (int) $conversation->guest_unread_count;
        }

        if ((int) $conversation->host_user_id === (int) $user->id) {
            return (int) $conversation->host_unread_count;
        }

        return 0;
    }

    public function syncUnreadCounters(Conversation $conversation): void
    {
        $conversation->update([
            'guest_unread_count' => $conversation->conversationMessages()
                ->where('recipient_type', 'guest')
                ->where('is_internal_note', false)
                ->whereNull('read_at')
                ->count(),
            'host_unread_count' => $conversation->conversationMessages()
                ->where('recipient_type', 'host')
                ->where('is_internal_note', false)
                ->whereNull('read_at')
                ->count(),
        ]);
    }
}
