<?php

namespace App\Services\Inventory;

use App\Models\BookingInventoryAssignment;
use App\Models\InventoryEvent;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use Illuminate\Support\Collection;

class InventoryEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function recordForItem(InventoryItem $item, string $eventKey, array $context = []): InventoryEvent
    {
        return InventoryEvent::query()->create([
            'inventory_item_id' => $item->id,
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
     * @param  array<string, mixed>  $context
     */
    public function recordForAssignment(BookingInventoryAssignment $assignment, string $eventKey, array $context = []): InventoryEvent
    {
        return InventoryEvent::query()->create([
            'inventory_item_id' => $assignment->inventory_item_id,
            'inventory_item_unit_id' => $assignment->inventory_item_unit_id,
            'booking_inventory_assignment_id' => $assignment->id,
            'booking_id' => $assignment->booking_id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? 'booking_inventory_assignment',
            'source_id' => $context['source_id'] ?? $assignment->id,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function recordForIssue(InventoryIssue $issue, string $eventKey, array $context = []): InventoryEvent
    {
        return InventoryEvent::query()->create([
            'inventory_item_id' => $issue->inventory_item_id,
            'inventory_item_unit_id' => $issue->inventory_item_unit_id,
            'inventory_issue_id' => $issue->id,
            'booking_id' => $issue->booking_id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? 'inventory_issue',
            'source_id' => $context['source_id'] ?? $issue->id,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context,
        ]);
    }

    /**
     * @return Collection<int, InventoryEvent>
     */
    public function getTimeline(InventoryItem $item): Collection
    {
        return $item->events()->orderBy('occurred_at')->orderBy('id')->get();
    }
}
