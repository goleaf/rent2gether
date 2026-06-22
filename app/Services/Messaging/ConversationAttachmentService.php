<?php

namespace App\Services\Messaging;

use App\Models\ConversationMessage;
use App\Models\ConversationMessageAttachment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class ConversationAttachmentService
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function attachPhoto(User $user, ConversationMessage $message, array $data): ConversationMessageAttachment
    {
        if (! app(ConversationPrivacyService::class)->canViewMessage($user, $message)) {
            throw new AuthorizationException(__('messages.errors.not_participant'));
        }

        $attachment = ConversationMessageAttachment::query()->create([
            'conversation_message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'uploaded_by_user_id' => $user->id,
            'attachment_type' => $data['attachment_type'] ?? 'photo',
            'media_type' => $data['media_type'] ?? 'photo',
            'path' => $data['path'] ?? null,
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'caption' => $data['caption'] ?? null,
            'linked_type' => $data['linked_type'] ?? null,
            'linked_id' => $data['linked_id'] ?? null,
            'visibility' => $data['visibility'] ?? 'guest_and_host',
        ]);

        app(ConversationEventService::class)->record($message->conversation, 'attachment_uploaded', [
            'user_id' => $user->id,
            'message_id' => $message->id,
            'attachment_id' => $attachment->id,
        ]);

        return $attachment;
    }

    public function attachSystemCard(ConversationMessage $message, string $linkedType, int $linkedId): ConversationMessageAttachment
    {
        return ConversationMessageAttachment::query()->create([
            'conversation_message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'attachment_type' => 'system_card',
            'linked_type' => $linkedType,
            'linked_id' => $linkedId,
            'visibility' => 'guest_and_host',
        ]);
    }

    public function getVisibleAttachments(User $user, ConversationMessage $message): Collection
    {
        return $message->attachments()
            ->get()
            ->filter(fn (ConversationMessageAttachment $attachment): bool => app(ConversationPrivacyService::class)->canViewAttachment($user, $attachment))
            ->values();
    }
}
