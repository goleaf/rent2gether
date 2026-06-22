<?php

namespace App\Services\Disputes;

use App\Models\DisputeCase;
use App\Models\DisputeEvent;
use Illuminate\Support\Collection;

class DisputeEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(DisputeCase $dispute, string $eventKey, array $context = []): DisputeEvent
    {
        return DisputeEvent::query()->create([
            'dispute_case_id' => $dispute->id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context,
        ]);
    }

    /**
     * @return Collection<int, DisputeEvent>
     */
    public function getTimeline(DisputeCase $dispute): Collection
    {
        return $dispute->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
