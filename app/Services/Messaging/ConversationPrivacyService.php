<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationMessageAttachment;
use App\Models\MessageTemplate;
use App\Models\User;

class ConversationPrivacyService
{
    public function canViewConversation(User $user, Conversation $conversation): bool
    {
        if ((int) $conversation->guest_user_id === (int) $user->id || (int) $conversation->host_user_id === (int) $user->id) {
            return true;
        }

        return $conversation->hasParticipant($user);
    }

    public function canWrite(User $user, Conversation $conversation): bool
    {
        if (! $this->canViewConversation($user, $conversation) || $conversation->is_read_only || $conversation->is_system_only) {
            return false;
        }

        if ((int) $conversation->guest_user_id === (int) $user->id) {
            return (bool) $conversation->guest_can_write;
        }

        if ((int) $conversation->host_user_id === (int) $user->id) {
            return (bool) $conversation->host_can_write;
        }

        return $conversation->participants()
            ->where('user_id', $user->id)
            ->where('can_write', true)
            ->exists();
    }

    public function canViewMessage(User $user, ConversationMessage $message): bool
    {
        if (! $this->canViewConversation($user, $message->conversation)) {
            return false;
        }

        if (! $message->is_internal_note) {
            return true;
        }

        return (int) $message->conversation->host_user_id === (int) $user->id;
    }

    public function canViewAttachment(User $user, ConversationMessageAttachment $attachment): bool
    {
        if ($attachment->visibility === 'future_review_only') {
            return false;
        }

        $conversation = $attachment->conversation;

        if (! $this->canViewConversation($user, $conversation)) {
            return false;
        }

        return match ($attachment->visibility) {
            'guest_and_host' => true,
            'guest_only' => (int) $conversation->guest_user_id === (int) $user->id,
            'host_only', 'internal' => (int) $conversation->host_user_id === (int) $user->id,
            default => false,
        };
    }

    public function canUseTemplate(User $user, Conversation $conversation, MessageTemplate $template): bool
    {
        if (! $this->canWrite($user, $conversation) || ! $template->active) {
            return false;
        }

        if ((int) $conversation->guest_user_id === (int) $user->id) {
            return (bool) $template->visible_to_guest;
        }

        if ((int) $conversation->host_user_id === (int) $user->id) {
            return (bool) $template->visible_to_host;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function filterConversationForUser(User $user, Conversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'conversation_number' => $conversation->conversation_number,
            'conversation_type' => $conversation->conversation_type,
            'status' => $conversation->status,
            'property_id' => $conversation->property_id,
            'room_id' => $conversation->room_id,
            'sleeping_place_id' => $conversation->sleeping_place_id,
            'booking_id' => $conversation->booking_id,
            'unread_count' => (int) $conversation->guest_user_id === (int) $user->id
                ? $conversation->guest_unread_count
                : $conversation->host_unread_count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterMessageForUser(User $user, ConversationMessage $message): array
    {
        if (! $this->canViewMessage($user, $message)) {
            return [];
        }

        return [
            'id' => $message->id,
            'message_type' => $message->message_type,
            'body' => $message->body,
            'translation_key' => $message->translation_key,
            'is_system' => $message->is_system,
            'is_important' => $message->is_important,
            'is_urgent' => $message->is_urgent,
            'sent_at' => $message->sent_at,
        ];
    }
}
