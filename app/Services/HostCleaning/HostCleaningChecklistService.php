<?php

namespace App\Services\HostCleaning;

use App\Models\HostCleaningTask;
use App\Models\HostCleaningTaskItem;
use App\Models\HostCleaningTemplate;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class HostCleaningChecklistService
{
    /**
     * @return Collection<int, HostCleaningTaskItem>
     */
    public function createDefaultChecklist(HostCleaningTask $task): Collection
    {
        $items = collect($this->defaultItems())
            ->map(fn (array $item, int $index): HostCleaningTaskItem => HostCleaningTaskItem::query()->firstOrCreate(
                [
                    'host_cleaning_task_id' => $task->id,
                    'item_key' => $item['item_key'],
                ],
                [
                    'label_key' => 'cleaning.checklist.'.$item['item_key'],
                    'required' => $item['required'],
                    'sort_order' => ($index + 1) * 10,
                    'status' => 'pending',
                ],
            ));

        return new Collection($items);
    }

    /**
     * @return Collection<int, HostCleaningTaskItem>
     */
    public function applyTemplate(HostCleaningTask $task, HostCleaningTemplate $template): Collection
    {
        if ((int) $task->user_id !== (int) $template->user_id) {
            throw new AuthorizationException;
        }

        $task->items()->delete();

        $items = collect($template->items_json ?: [])
            ->map(fn (array $item, int $index): HostCleaningTaskItem => HostCleaningTaskItem::query()->create([
                'host_cleaning_task_id' => $task->id,
                'item_key' => (string) $item['item_key'],
                'label_key' => $item['label_key'] ?? 'cleaning.checklist.'.$item['item_key'],
                'required' => (bool) ($item['required'] ?? false),
                'sort_order' => (int) ($item['sort_order'] ?? (($index + 1) * 10)),
                'status' => 'pending',
            ]));

        return new Collection($items);
    }

    public function markItemCompleted(User $user, HostCleaningTask $task, string $itemKey): HostCleaningTaskItem
    {
        $this->authorize($user, $task);

        return tap($this->findOrCreateItem($task, $itemKey), function (HostCleaningTaskItem $item) use ($user): void {
            $item->forceFill([
                'status' => 'done',
                'completed_by_user_id' => $user->id,
                'completed_at' => now(),
            ])->save();
        });
    }

    public function markItemIncomplete(User $user, HostCleaningTask $task, string $itemKey): HostCleaningTaskItem
    {
        $this->authorize($user, $task);

        return tap($this->findOrCreateItem($task, $itemKey), function (HostCleaningTaskItem $item): void {
            $item->forceFill([
                'status' => 'pending',
                'completed_by_user_id' => null,
                'completed_at' => null,
            ])->save();
        });
    }

    /**
     * @return Collection<int, HostCleaningTaskItem>
     */
    public function getMissingRequiredItems(HostCleaningTask $task): Collection
    {
        return $task->items()
            ->where('required', true)
            ->where('status', '!=', 'done')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return list<array{item_key:string, required:bool}>
     */
    public function defaultItems(): array
    {
        return [
            ['item_key' => 'replace_bedding', 'required' => true],
            ['item_key' => 'replace_towel', 'required' => true],
            ['item_key' => 'check_pillow', 'required' => true],
            ['item_key' => 'check_blanket', 'required' => true],
            ['item_key' => 'wipe_dust', 'required' => true],
            ['item_key' => 'take_out_trash', 'required' => true],
            ['item_key' => 'vacuum', 'required' => true],
            ['item_key' => 'mop_floor', 'required' => true],
            ['item_key' => 'ventilate_room', 'required' => false],
            ['item_key' => 'clean_kitchen', 'required' => false],
            ['item_key' => 'clean_bathroom', 'required' => false],
            ['item_key' => 'clean_toilet', 'required' => false],
            ['item_key' => 'check_locker', 'required' => true],
            ['item_key' => 'check_bed', 'required' => true],
            ['item_key' => 'check_mattress', 'required' => true],
            ['item_key' => 'check_socket', 'required' => false],
            ['item_key' => 'check_lamp', 'required' => false],
            ['item_key' => 'check_curtain', 'required' => false],
            ['item_key' => 'check_forgotten_items', 'required' => true],
            ['item_key' => 'upload_after_photos', 'required' => true],
        ];
    }

    private function findOrCreateItem(HostCleaningTask $task, string $itemKey): HostCleaningTaskItem
    {
        return HostCleaningTaskItem::query()->firstOrCreate(
            [
                'host_cleaning_task_id' => $task->id,
                'item_key' => $itemKey,
            ],
            [
                'label_key' => 'cleaning.checklist.'.$itemKey,
                'required' => in_array($itemKey, ['upload_after_photos', 'replace_bedding', 'replace_towel'], true),
                'sort_order' => 999,
                'status' => 'pending',
            ],
        );
    }

    private function authorize(User $user, HostCleaningTask $task): void
    {
        if ((int) $task->user_id !== (int) $user->id && (int) $task->assigned_to_user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }
}
