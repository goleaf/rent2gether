<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;
use App\Models\CleaningTaskItem;
use App\Models\User;
use Illuminate\Support\Collection;

class CleaningTaskItemService
{
    /**
     * @return array<int, string>
     */
    public static function defaultItemKeys(): array
    {
        return [
            'replace_bedding',
            'replace_towel',
            'wipe_dust',
            'take_out_trash',
            'clean_kitchen',
            'clean_bathroom',
            'clean_toilet',
            'check_locker',
            'check_bed',
            'check_mattress',
            'check_socket',
            'check_lamp',
            'check_privacy_curtain',
            'check_shelf',
            'check_hook',
            'check_shoe_place',
            'check_luggage_place',
            'ventilate_room',
            'check_smell',
            'check_mold',
            'check_insects',
            'upload_after_photos',
        ];
    }

    public function createDefaultItems(CleaningTask $task): Collection
    {
        return collect(self::defaultItemKeys())
            ->values()
            ->map(function (string $itemKey, int $index) use ($task): CleaningTaskItem {
                return CleaningTaskItem::query()->firstOrCreate(
                    [
                        'cleaning_task_id' => $task->id,
                        'item_key' => $itemKey,
                    ],
                    [
                        'label_key' => 'cleaning.items.'.$itemKey,
                        'required' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            });
    }

    public function markCompleted(CleaningTask $task, string $itemKey, ?User $user = null): CleaningTaskItem
    {
        $item = $task->items()->where('item_key', $itemKey)->firstOrFail();
        $item->forceFill([
            'status' => 'completed',
            'completed_by_user_id' => $user?->id,
            'completed_at' => now(),
        ])->save();

        app(CleaningEventService::class)->record($task, 'checklist_item_completed', [
            'user_id' => $user?->id,
            'item_key' => $itemKey,
        ]);

        return $item->refresh();
    }

    public function markSkipped(CleaningTask $task, string $itemKey, ?User $user = null): CleaningTaskItem
    {
        $item = $task->items()->where('item_key', $itemKey)->firstOrFail();
        $item->forceFill([
            'status' => 'skipped',
            'completed_by_user_id' => $user?->id,
            'completed_at' => now(),
        ])->save();

        return $item->refresh();
    }

    public function getRequiredIncompleteItems(CleaningTask $task): Collection
    {
        return $task->items()
            ->where('required', true)
            ->whereNotIn('status', ['completed', 'not_required'])
            ->get();
    }

    public function isChecklistCompleted(CleaningTask $task): bool
    {
        return $this->getRequiredIncompleteItems($task)->isEmpty();
    }
}
