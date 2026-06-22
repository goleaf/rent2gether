<?php

namespace App\Services\Notifications;

use App\Models\ConversationMessage;
use App\Models\User;

class MessageNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyNewMessage(ConversationMessage $message): void
    {
        $message->loadMissing('conversation');
        $conversation = $message->conversation;
        $booking = $this->bookingFrom($conversation);
        $recipientId = (int) $message->sender_user_id === (int) $conversation?->guest_user_id
            ? $conversation?->host_user_id
            : $conversation?->guest_user_id;
        $recipient = $recipientId ? User::query()->find($recipientId) : null;

        if ($recipient instanceof User) {
            app(NotificationService::class)->createForUser($recipient, (int) $recipient->id === (int) $conversation?->host_user_id ? 'guest_sent_message' : 'host_sent_message', [
                'booking' => $booking,
                'recipient_type' => (int) $recipient->id === (int) $conversation?->host_user_id ? 'host' : 'guest',
            ]);
        }
    }

    public function notifyUrgentMessage(ConversationMessage $message): void
    {
        $this->notifyNewMessage($message);
    }

    public function notifyUnreadDigest(User $user): void
    {
        app(NotificationService::class)->createForUser($user, 'guest_sent_message', [
            'notification_type' => 'digest',
            'priority' => 'normal',
        ]);
    }
}
