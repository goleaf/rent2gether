<?php

namespace App\Services\Messaging;

use App\Models\ConversationMessage;
use App\Models\ConversationSystemEvent;
use App\Models\MessageTemplateUsage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

class ConversationNotificationService
{
    public function notifyNewMessage(ConversationMessage $message): void
    {
        $recipient = $message->recipient;

        if ($recipient instanceof User) {
            $this->create($recipient, 'conversation_message', $message);
        }
    }

    public function notifyUrgentMessage(ConversationMessage $message): void
    {
        $recipient = $message->recipient;

        if ($recipient instanceof User) {
            $this->create($recipient, 'conversation_urgent_message', $message);
        }
    }

    public function notifyTemplateActionCreated(MessageTemplateUsage $usage): void
    {
        $host = $usage->conversation?->host;

        if ($host instanceof User) {
            $this->create($host, 'conversation_template_action', $usage->message);
        }
    }

    public function notifySystemEvent(ConversationSystemEvent $event): void
    {
        foreach ([$event->conversation?->guest, $event->conversation?->host] as $recipient) {
            if ($recipient instanceof User) {
                $this->create($recipient, 'conversation_system_event', $event->message);
            }
        }
    }

    private function create(User $recipient, string $type, ?ConversationMessage $message): void
    {
        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $recipient->id,
            'user_id' => $recipient->id,
            'data' => [
                'conversation_id' => $message?->conversation_id,
                'message_id' => $message?->id,
            ],
            'title_key' => 'notifications.'.$type.'.title',
            'body_key' => 'notifications.'.$type.'.body',
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }
}
