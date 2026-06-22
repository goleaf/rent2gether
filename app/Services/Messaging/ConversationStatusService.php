<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\ConversationStatusLog;
use App\Models\User;

class ConversationStatusService
{
    /** @var list<string> */
    private array $statuses = [
        'active',
        'waiting_guest_response',
        'waiting_host_response',
        'closed',
        'archived',
        'blocked',
        'muted',
        'read_only',
        'system_only',
    ];

    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(Conversation $conversation, string $newStatus, ?User $user = null, array $context = []): Conversation
    {
        if (! $this->canTransition($conversation, $newStatus)) {
            return $conversation;
        }

        $oldStatus = $conversation->status;

        $conversation->update([
            'status' => $newStatus,
            'is_read_only' => in_array($newStatus, ['closed', 'read_only', 'system_only'], true),
            'is_system_only' => $newStatus === 'system_only',
            'closed_at' => $newStatus === 'closed' ? now() : $conversation->closed_at,
        ]);

        ConversationStatusLog::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $conversation->refresh();
    }

    public function canTransition(Conversation $conversation, string $newStatus): bool
    {
        return in_array($newStatus, $this->statuses, true);
    }

    public function syncConversationStatusFromSource(Conversation $conversation): void
    {
        if ($conversation->closed_at !== null && $conversation->status !== 'closed') {
            $conversation->update(['status' => 'closed']);
        }
    }
}
