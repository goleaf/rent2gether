<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\User;

class NotificationPrivacyService
{
    public function canView(User $user, Notification $notification): bool
    {
        return (int) ($notification->recipient_user_id ?: $notification->user_id) === (int) $user->id;
    }

    public function canAct(User $user, Notification $notification): bool
    {
        return $this->canView($user, $notification)
            && ! $notification->expired_at
            && ! in_array($notification->status, ['expired', 'cancelled', 'archived'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForUser(User $user, Notification $notification): array
    {
        if (! $this->canView($user, $notification)) {
            return [];
        }

        return [
            'id' => $notification->id,
            'title' => $notification->title($notification->locale),
            'body' => $notification->body($notification->locale),
            'category' => $notification->notification_category,
            'priority' => $notification->priority,
            'action_type' => $notification->action_type,
            'action_url' => $this->canAct($user, $notification) ? $notification->safe_action_url : null,
            'payload' => $this->hideSensitivePayload($notification),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function hideSensitivePayload(Notification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];
        $payload = $data['payload'] ?? $data;

        if (! is_array($payload)) {
            return [];
        }

        foreach ([
            'door_code',
            'key_safe_code',
            'intercom_code',
            'access_code',
            'exact_address',
            'provider_payload',
            'payment_payload',
            'internal_note',
        ] as $key) {
            unset($payload[$key]);
        }

        return $payload;
    }
}
