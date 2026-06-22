<?php

namespace App\Services\Cleaning;

use App\Models\InspectionEvent;
use App\Models\InspectionTask;
use Illuminate\Support\Collection;

class InspectionEventService
{
    public function record(InspectionTask $task, string $eventKey, array $context = []): InspectionEvent
    {
        return InspectionEvent::query()->create([
            'inspection_task_id' => $task->id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context === [] ? null : $context,
        ]);
    }

    public function getTimeline(InspectionTask $task): Collection
    {
        return $task->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
