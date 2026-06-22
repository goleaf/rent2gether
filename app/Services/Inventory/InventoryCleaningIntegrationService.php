<?php

namespace App\Services\Inventory;

use App\Models\CleaningTask;
use App\Models\CleaningTaskIssue;
use App\Models\InventoryCheck;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;

class InventoryCleaningIntegrationService
{
    public function checkInventoryDuringCleaning(CleaningTask $task): InventoryCheck
    {
        $check = app(InventoryCheckService::class)->createForCleaning($task);
        app(InventoryCheckItemService::class)->createExpectedItems($check);

        return $check->refresh();
    }

    public function markBeddingNeedsWashing(CleaningTask $task): void
    {
        $this->markItems($task, ['bedding_set', 'bed_sheet', 'pillowcase'], 'needs_washing');
    }

    public function markTowelsNeedsWashing(CleaningTask $task): void
    {
        $this->markItems($task, ['towel'], 'needs_washing');
    }

    public function createIssueFromCleaning(CleaningTaskIssue $issue): ?InventoryIssue
    {
        return app(InventoryIssueService::class)->createFromCleaningIssue($issue);
    }

    /**
     * @param  array<int, string>  $types
     */
    private function markItems(CleaningTask $task, array $types, string $status): void
    {
        InventoryItem::query()
            ->where('property_id', $task->property_id)
            ->where('sleeping_place_id', $task->sleeping_place_id)
            ->whereIn('item_type', $types)
            ->get()
            ->each(fn (InventoryItem $item) => app(InventoryStatusService::class)->transitionItem($item, $status));
    }
}
