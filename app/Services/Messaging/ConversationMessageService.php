<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\ConversationInternalNote;
use App\Models\ConversationMessage;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ConversationMessageService
{
    public function __construct(
        private readonly ConversationNumberService $numbers,
        private readonly ConversationPrivacyService $privacy,
        private readonly ConversationEventService $events,
        private readonly ConversationNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     *
     * @throws AuthorizationException
     */
    public function sendText(User $sender, Conversation $conversation, string $body, array $context = []): ConversationMessage
    {
        $this->ensureCanWrite($sender, $conversation);

        app(ConversationSafetyService::class)->checkMessageBeforeSend($sender, $conversation, $body);

        return $this->createUserMessage($sender, $conversation, [
            'message_type' => 'text',
            'body' => trim($body),
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'is_important' => (bool) ($context['is_important'] ?? false),
            'is_urgent' => (bool) ($context['is_urgent'] ?? false),
            'sent_at' => $context['sent_at'] ?? now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $params
     *
     * @throws AuthorizationException
     */
    public function sendTemplate(User $sender, Conversation $conversation, string $templateKey, array $params = []): ConversationMessage
    {
        $template = app(MessageTemplateService::class)->getByKey($templateKey);

        if (! $template instanceof MessageTemplate || ! app(MessageTemplateService::class)->canUseTemplate($sender, $conversation, $template)) {
            throw new AuthorizationException(__('messages.errors.template_unavailable'));
        }

        $message = $this->createUserMessage($sender, $conversation, [
            'message_type' => 'quick_template',
            'body' => __($template->body_translation_key, $params),
            'template_key' => $template->template_key,
            'source_type' => $params['source_type'] ?? $conversation->conversation_type,
            'source_id' => $params['source_id'] ?? $conversation->booking_id,
            'sent_at' => $params['sent_at'] ?? now(),
        ]);

        app(MessageTemplateUsageService::class)->recordUsage($template, $message, $sender);

        return $message;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function sendSystemEvent(Conversation $conversation, string $eventKey, array $params = []): ConversationMessage
    {
        $translationKey = $params['translation_key'] ?? "messages.system_events.{$eventKey}";
        $importance = $params['importance_level'] ?? 'normal';

        $message = ConversationMessage::query()->create([
            'message_number' => $this->numbers->generateMessageNumber(),
            'conversation_id' => $conversation->id,
            'sender_user_id' => null,
            'sender_type' => $params['sender_type'] ?? 'system',
            'recipient_user_id' => null,
            'recipient_type' => null,
            'message_type' => 'system_event',
            'status' => 'system',
            'body' => null,
            'translation_key' => $translationKey,
            'translation_params_json' => $params['translation_params_json'] ?? [],
            'source_type' => $params['source_type'] ?? 'system',
            'source_id' => $params['source_id'] ?? null,
            'booking_id' => $params['booking_id'] ?? $conversation->booking_id,
            'property_id' => $conversation->property_id,
            'room_id' => $conversation->room_id,
            'sleeping_place_id' => $conversation->sleeping_place_id,
            'is_system' => true,
            'is_important' => in_array($importance, ['important', 'urgent', 'critical'], true),
            'is_urgent' => in_array($importance, ['urgent', 'critical'], true),
            'sent_at' => $params['occurred_at'] ?? now(),
        ]);

        $this->afterMessageCreated($message, updateUnread: false);

        return $message;
    }

    /**
     * @param  array<string, mixed>  $context
     *
     * @throws AuthorizationException
     */
    public function sendInternalNote(User $host, Conversation $conversation, string $note, array $context = []): ConversationMessage
    {
        if ((int) $conversation->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('messages.errors.not_participant'));
        }

        ConversationInternalNote::query()->create([
            'conversation_id' => $conversation->id,
            'booking_id' => $conversation->booking_id,
            'guest_user_id' => $conversation->guest_user_id,
            'host_user_id' => $conversation->host_user_id,
            'property_id' => $conversation->property_id,
            'room_id' => $conversation->room_id,
            'sleeping_place_id' => $conversation->sleeping_place_id,
            'note' => $note,
            'note_type' => $context['note_type'] ?? 'booking_note',
            'created_by_user_id' => $host->id,
            'visible_to_host' => true,
            'visible_to_guest' => false,
            'internal' => true,
        ]);

        $message = ConversationMessage::query()->create([
            'message_number' => $this->numbers->generateMessageNumber(),
            'conversation_id' => $conversation->id,
            'sender_user_id' => $host->id,
            'sender_type' => 'host',
            'recipient_user_id' => null,
            'recipient_type' => 'host',
            'message_type' => 'internal_note',
            'status' => 'sent',
            'body' => $note,
            'booking_id' => $conversation->booking_id,
            'property_id' => $conversation->property_id,
            'room_id' => $conversation->room_id,
            'sleeping_place_id' => $conversation->sleeping_place_id,
            'is_internal_note' => true,
            'original_locale' => app()->getLocale(),
            'sent_at' => now(),
        ]);

        $this->afterMessageCreated($message, updateUnread: false);

        return $message;
    }

    public function markFailed(ConversationMessage $message, string $reason): ConversationMessage
    {
        $message->update([
            'status' => 'failed',
            'failed_at' => now(),
            'translation_status' => $reason,
        ]);

        return $message->refresh();
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteBySender(User $sender, ConversationMessage $message): ConversationMessage
    {
        if ((int) $message->sender_user_id !== (int) $sender->id) {
            throw new AuthorizationException(__('messages.errors.not_participant'));
        }

        $message->update([
            'status' => 'deleted_by_sender',
            'deleted_at' => now(),
        ]);

        return $message->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUserMessage(User $sender, Conversation $conversation, array $attributes): ConversationMessage
    {
        if (($attributes['body'] ?? '') === '') {
            throw ValidationException::withMessages([
                'body' => __('messages.errors.empty_message'),
            ]);
        }

        $senderType = $this->senderType($sender, $conversation);
        [$recipientId, $recipientType] = $this->recipientFor($sender, $conversation);

        $message = ConversationMessage::query()->create([
            'message_number' => $this->numbers->generateMessageNumber(),
            'conversation_id' => $conversation->id,
            'sender_user_id' => $sender->id,
            'sender_type' => $senderType,
            'recipient_user_id' => $recipientId,
            'recipient_type' => $recipientType,
            'message_type' => $attributes['message_type'],
            'status' => 'sent',
            'body' => $attributes['body'],
            'template_key' => $attributes['template_key'] ?? null,
            'source_type' => $attributes['source_type'] ?? null,
            'source_id' => $attributes['source_id'] ?? null,
            'booking_id' => $conversation->booking_id,
            'property_id' => $conversation->property_id,
            'room_id' => $conversation->room_id,
            'sleeping_place_id' => $conversation->sleeping_place_id,
            'is_important' => (bool) ($attributes['is_important'] ?? false),
            'is_urgent' => (bool) ($attributes['is_urgent'] ?? false),
            'is_system' => false,
            'is_internal_note' => false,
            'original_locale' => app()->getLocale(),
            'sent_at' => $attributes['sent_at'] ?? now(),
        ]);

        $this->afterMessageCreated($message);

        return $message;
    }

    private function afterMessageCreated(ConversationMessage $message, bool $updateUnread = true): void
    {
        $conversation = $message->conversation;
        $updates = [
            'last_message_id' => $message->id,
            'last_message_at' => $message->sent_at ?: now(),
            'last_message_sender_user_id' => $message->sender_user_id,
            'has_urgent_messages' => $conversation->has_urgent_messages || $message->is_urgent,
            'has_important_messages' => $conversation->has_important_messages || $message->is_important,
        ];

        if ($updateUnread && ! $message->is_internal_note) {
            if ($message->recipient_type === 'host') {
                $updates['host_unread_count'] = ((int) $conversation->host_unread_count) + 1;
            }

            if ($message->recipient_type === 'guest') {
                $updates['guest_unread_count'] = ((int) $conversation->guest_unread_count) + 1;
            }
        }

        $conversation->update($updates);
        $this->events->record($conversation, $message->is_urgent ? 'urgent_message_sent' : 'message_sent', [
            'user_id' => $message->sender_user_id,
            'message_id' => $message->id,
        ]);

        if ($message->is_urgent) {
            $this->notifications->notifyUrgentMessage($message->refresh());
        } elseif (! $message->is_system && ! $message->is_internal_note) {
            $this->notifications->notifyNewMessage($message->refresh());
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureCanWrite(User $sender, Conversation $conversation): void
    {
        if (! $this->privacy->canWrite($sender, $conversation)) {
            throw new AuthorizationException(__('messages.errors.not_participant'));
        }
    }

    private function senderType(User $sender, Conversation $conversation): string
    {
        return match (true) {
            (int) $conversation->guest_user_id === (int) $sender->id => 'guest',
            (int) $conversation->host_user_id === (int) $sender->id => 'host',
            default => 'future_user',
        };
    }

    /**
     * @return array{int|null, string|null}
     */
    private function recipientFor(User $sender, Conversation $conversation): array
    {
        if ((int) $conversation->guest_user_id === (int) $sender->id) {
            return [$conversation->host_user_id, 'host'];
        }

        if ((int) $conversation->host_user_id === (int) $sender->id) {
            return [$conversation->guest_user_id, 'guest'];
        }

        return [null, null];
    }
}
