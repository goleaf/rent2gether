<?php

namespace App\Services\Cleaning;

use App\Models\InspectionTask;
use App\Models\InspectionTaskItem;
use App\Models\User;
use Illuminate\Support\Collection;

class InspectionTaskItemService
{
    /**
     * @return array<int, string>
     */
    public static function defaultItemKeys(): array
    {
        return [
            'sleeping_place_clean',
            'mattress_clean',
            'bedding_ready',
            'towel_ready',
            'locker_empty',
            'locker_lock_working',
            'socket_working',
            'lamp_working',
            'room_clean',
            'bathroom_clean',
            'kitchen_clean',
            'no_bad_smell',
            'no_mold',
            'no_insects',
            'no_damage',
            'inventory_complete',
            'access_code_working',
            'key_available',
            'photos_uploaded',
        ];
    }

    public function createDefaultItems(InspectionTask $task): Collection
    {
        return collect(self::defaultItemKeys())
            ->values()
            ->map(function (string $itemKey, int $index) use ($task): InspectionTaskItem {
                return InspectionTaskItem::query()->firstOrCreate(
                    [
                        'inspection_task_id' => $task->id,
                        'item_key' => $itemKey,
                    ],
                    [
                        'label_key' => 'inspections.items.'.$itemKey,
                        'required' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            });
    }

    public function markCompleted(InspectionTask $task, string $itemKey, ?User $user = null, ?string $resultValue = null): InspectionTaskItem
    {
        $item = $task->items()->where('item_key', $itemKey)->firstOrFail();
        $item->forceFill([
            'status' => 'completed',
            'completed_by_user_id' => $user?->id,
            'completed_at' => now(),
            'result_value' => $resultValue,
        ])->save();

        app(InspectionEventService::class)->record($task, 'checklist_item_completed', [
            'user_id' => $user?->id,
            'item_key' => $itemKey,
        ]);

        return $item->refresh();
    }

    public function markFailed(InspectionTask $task, string $itemKey, ?User $user = null, ?string $note = null): InspectionTaskItem
    {
        $item = $task->items()->where('item_key', $itemKey)->firstOrFail();
        $item->forceFill([
            'status' => 'failed',
            'completed_by_user_id' => $user?->id,
            'completed_at' => now(),
            'note' => $note,
        ])->save();

        return $item->refresh();
    }

    public function getFailedItems(InspectionTask $task): Collection
    {
        return $task->items()->where('status', 'failed')->get();
    }

    public function isChecklistCompleted(InspectionTask $task): bool
    {
        return ! $task->items()
            ->where('required', true)
            ->whereNotIn('status', ['completed', 'not_required'])
            ->exists();
    }
}
