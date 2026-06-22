<?php

namespace App\Services\Inventory;

use App\Models\BookingInventoryAssignment;
use App\Models\InventoryCheck;
use App\Models\InventoryCheckItem;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use Illuminate\Support\Collection;

class InventoryCheckItemService
{
    /**
     * @return Collection<int, InventoryCheckItem>
     */
    public function createExpectedItems(InventoryCheck $check): Collection
    {
        $returnableAssignmentItemIds = BookingInventoryAssignment::query()
            ->where('booking_id', $check->booking_id)
            ->where('expected_return', true)
            ->pluck('inventory_item_id')
            ->all();

        $items = InventoryItem::query()
            ->where('property_id', $check->property_id)
            ->when($check->room_id, fn ($query) => $query->where(function ($nested) use ($check): void {
                $nested->whereNull('room_id')->orWhere('room_id', $check->room_id);
            }))
            ->when($check->sleeping_place_id, fn ($query) => $query->where(function ($nested) use ($check): void {
                $nested->whereNull('sleeping_place_id')->orWhere('sleeping_place_id', $check->sleeping_place_id);
            }))
            ->where(function ($query) use ($returnableAssignmentItemIds): void {
                $query->where('is_required_for_readiness', true)
                    ->orWhere('is_returnable', true)
                    ->when($returnableAssignmentItemIds !== [], fn ($nested) => $nested->orWhereIn('id', $returnableAssignmentItemIds));
            })
            ->orderBy('id')
            ->get();

        $rows = $items->map(function (InventoryItem $item) use ($check): InventoryCheckItem {
            return InventoryCheckItem::query()->firstOrCreate(
                [
                    'inventory_check_id' => $check->id,
                    'inventory_item_id' => $item->id,
                    'inventory_item_unit_id' => null,
                ],
                [
                    'expected_present' => true,
                    'is_present' => ! in_array($item->status, ['missing', 'lost'], true),
                    'expected_return' => $item->is_returnable,
                    'is_returned' => false,
                    'expected_condition_status' => $item->condition_status,
                ],
            );
        });

        $check->forceFill(['items_expected_count' => $rows->count()])->save();

        return $rows;
    }

    public function markPresent(InventoryCheckItem $item): InventoryCheckItem
    {
        $item->forceFill(['is_present' => true, 'missing' => false])->save();

        return $item->refresh();
    }

    public function markMissing(InventoryCheckItem $item, ?string $note = null): InventoryCheckItem
    {
        $item->forceFill(['is_present' => false, 'missing' => true, 'note' => $note])->save();

        return $item->refresh();
    }

    public function markReturned(InventoryCheckItem $item): InventoryCheckItem
    {
        $item->forceFill(['is_returned' => true, 'missing' => false])->save();

        return $item->refresh();
    }

    public function markDamaged(InventoryCheckItem $item, ?string $note = null): InventoryCheckItem
    {
        $item->forceFill(['damaged' => true, 'actual_condition_status' => 'damaged', 'note' => $note])->save();

        return $item->refresh();
    }

    public function markNeedsRepair(InventoryCheckItem $item): InventoryCheckItem
    {
        $item->forceFill(['needs_repair' => true])->save();

        return $item->refresh();
    }

    public function markNeedsReplacement(InventoryCheckItem $item): InventoryCheckItem
    {
        $item->forceFill(['needs_replacement' => true])->save();

        return $item->refresh();
    }

    /**
     * @return Collection<int, InventoryIssue>
     */
    public function createIssuesFromFailedCheckItems(InventoryCheck $check): Collection
    {
        $issues = collect();

        foreach ($check->items()->where(function ($query): void {
            $query->where('missing', true)
                ->orWhere('damaged', true)
                ->orWhere('needs_repair', true)
                ->orWhere('needs_replacement', true);
        })->get() as $checkItem) {
            $type = match (true) {
                $checkItem->missing => 'missing',
                $checkItem->damaged => 'damaged',
                $checkItem->needs_repair => 'needs_repair',
                default => 'needs_replacement',
            };
            $issues->push(app(InventoryIssueService::class)->createFromCheckItem($checkItem, $type));
        }

        if ($issues->isNotEmpty()) {
            app(InventoryCheckService::class)->markCompletedWithIssues($check);
        }

        return $issues;
    }
}
