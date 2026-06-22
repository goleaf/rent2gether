<?php

namespace App\Services\Inventory;

use App\Models\BookingInventoryAssignment;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use App\Models\InventoryItemUnit;
use App\Models\InventoryStatusLog;
use App\Models\User;

class InventoryStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transitionItem(InventoryItem $item, string $newStatus, ?User $user = null, array $context = []): InventoryItem
    {
        $oldStatus = $item->status;
        $item->forceFill(['status' => $newStatus])->save();
        $this->log(['inventory_item_id' => $item->id], $oldStatus, $newStatus, $user, $context);

        return $item->refresh();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function transitionUnit(InventoryItemUnit $unit, string $newStatus, ?User $user = null, array $context = []): InventoryItemUnit
    {
        $oldStatus = $unit->status;
        $unit->forceFill(['status' => $newStatus])->save();
        $this->log(['inventory_item_unit_id' => $unit->id], $oldStatus, $newStatus, $user, $context);

        return $unit->refresh();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function transitionAssignment(BookingInventoryAssignment $assignment, string $newStatus, ?User $user = null, array $context = []): BookingInventoryAssignment
    {
        $oldStatus = $assignment->status;
        $assignment->forceFill(['status' => $newStatus])->save();
        $this->log(['booking_inventory_assignment_id' => $assignment->id], $oldStatus, $newStatus, $user, $context);

        return $assignment->refresh();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function transitionIssue(InventoryIssue $issue, string $newStatus, ?User $user = null, array $context = []): InventoryIssue
    {
        $oldStatus = $issue->status;
        $issue->forceFill(['status' => $newStatus])->save();
        $this->log(['inventory_issue_id' => $issue->id], $oldStatus, $newStatus, $user, $context);

        return $issue->refresh();
    }

    /**
     * @param  array<string, int|null>  $keys
     * @param  array<string, mixed>  $context
     */
    private function log(array $keys, ?string $oldStatus, string $newStatus, ?User $user, array $context): void
    {
        InventoryStatusLog::query()->create(array_merge($keys, [
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]));
    }
}
