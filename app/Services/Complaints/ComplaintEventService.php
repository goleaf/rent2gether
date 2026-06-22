<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;
use App\Models\ComplaintEvent;
use Illuminate\Support\Collection;

class ComplaintEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(ComplaintCase $case, string $eventKey, array $context = []): ComplaintEvent
    {
        return ComplaintEvent::query()->create([
            'complaint_case_id' => $case->id,
            'event_key' => $eventKey,
            'event_type' => (string) ($context['event_type'] ?? 'system'),
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context,
        ]);
    }

    /**
     * @return Collection<int, ComplaintEvent>
     */
    public function getTimeline(ComplaintCase $case): Collection
    {
        return $case->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
