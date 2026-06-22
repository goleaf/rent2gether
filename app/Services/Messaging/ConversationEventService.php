<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\ConversationEvent;
use Illuminate\Support\Collection;

class ConversationEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(Conversation $conversation, string $eventKey, array $context = []): ConversationEvent
    {
        return ConversationEvent::query()->create([
            'conversation_id' => $conversation->id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context,
        ]);
    }

    public function getTimeline(Conversation $conversation): Collection
    {
        return $conversation->events()
            ->orderByDesc('occurred_at')
            ->get();
    }
}
