<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;
use App\Models\CleaningTaskIssue;
use Illuminate\Support\Collection;

class CleaningInventoryIntegrationService
{
    public function checkInventoryDuringCleaning(CleaningTask $task): Collection
    {
        return $task->items()
            ->whereIn('item_key', ['check_locker', 'check_bed', 'check_mattress'])
            ->get();
    }

    public function createInventoryIssueFromCleaning(CleaningTaskIssue $issue): mixed
    {
        $issue->forceFill(['creates_deposit_review' => true])->save();

        return app(CleaningTaskIssueService::class)->createDepositReviewIfNeeded($issue);
    }

    public function markConsumablesUsed(CleaningTask $task, array $items): void
    {
        $task->forceFill([
            'supplies_required' => false,
            'supplies_note' => implode(', ', $items),
        ])->save();
    }
}
