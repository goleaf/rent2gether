<?php

namespace App\Services\Inventory;

use App\Models\InventoryIssue;
use App\Models\InventoryReplacement;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class InventoryReplacementService
{
    public function __construct(
        private readonly InventoryNumberService $numbers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createReplacement(User $host, InventoryIssue $issue, array $data): InventoryReplacement
    {
        if ((int) $issue->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('inventory.validation.host_must_own_inventory'));
        }

        $newItemId = null;

        if (isset($data['new_item']) && is_array($data['new_item'])) {
            $newItem = app(InventoryItemService::class)->createItem($host, array_merge([
                'property_id' => $issue->property_id,
                'room_id' => $issue->room_id,
                'sleeping_place_id' => $issue->sleeping_place_id,
                'status' => 'active',
                'condition_status' => 'new',
                'quantity' => $issue->quantity_affected,
                'unit' => $issue->inventoryItem->unit,
                'is_guest_visible' => $issue->inventoryItem->is_guest_visible,
                'is_required_for_readiness' => $issue->inventoryItem->is_required_for_readiness,
                'is_promised_in_listing' => $issue->inventoryItem->is_promised_in_listing,
                'current_location_type' => $issue->sleeping_place_id ? 'sleeping_place' : 'property',
                'currency' => $data['currency'] ?? $issue->currency,
            ], $data['new_item']));
            $newItemId = $newItem->id;
        }

        $replacement = InventoryReplacement::query()->create([
            'replacement_number' => $data['replacement_number'] ?? $this->numbers->generateReplacementNumber(),
            'old_inventory_item_id' => $issue->inventory_item_id,
            'old_inventory_item_unit_id' => $issue->inventory_item_unit_id,
            'new_inventory_item_id' => $newItemId,
            'new_inventory_item_unit_id' => $data['new_inventory_item_unit_id'] ?? null,
            'inventory_issue_id' => $issue->id,
            'maintenance_request_id' => $issue->maintenance_request_created_id,
            'booking_deposit_deduction_id' => $issue->booking_deposit_deduction_id,
            'host_user_id' => $host->id,
            'property_id' => $issue->property_id,
            'room_id' => $issue->room_id,
            'sleeping_place_id' => $issue->sleeping_place_id,
            'replacement_reason' => $data['replacement_reason'] ?? $issue->issue_type,
            'status' => $data['status'] ?? 'planned',
            'replacement_cost_amount' => $data['replacement_cost_amount'] ?? $issue->replacement_cost_amount,
            'currency' => $data['currency'] ?? $issue->currency,
            'note' => $data['note'] ?? null,
        ]);

        $issue->forceFill(['status' => 'replacement_created'])->save();
        app(InventoryEventService::class)->recordForIssue($issue->refresh(), 'replacement_created', ['source_id' => $replacement->id]);

        return $replacement->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function markPurchased(InventoryReplacement $replacement, array $data = []): InventoryReplacement
    {
        $replacement->forceFill([
            'status' => 'purchased',
            'purchased_at' => $data['purchased_at'] ?? now(),
            'replacement_cost_amount' => $data['replacement_cost_amount'] ?? $replacement->replacement_cost_amount,
        ])->save();

        return $replacement->refresh();
    }

    public function markInstalled(InventoryReplacement $replacement): InventoryReplacement
    {
        $replacement->forceFill([
            'status' => 'installed',
            'replaced_at' => now(),
        ])->save();

        return $replacement->refresh();
    }

    public function completeReplacement(InventoryReplacement $replacement): InventoryReplacement
    {
        if ($replacement->oldInventoryItem) {
            app(InventoryItemService::class)->markRetired($replacement->oldInventoryItem);
        }

        if ($replacement->newInventoryItem) {
            app(InventoryItemService::class)->markAvailable($replacement->newInventoryItem);
        }

        $replacement->forceFill([
            'status' => 'completed',
            'replaced_at' => $replacement->replaced_at ?? now(),
        ])->save();

        if ($replacement->inventoryIssue) {
            app(InventoryEventService::class)->recordForIssue($replacement->inventoryIssue, 'replacement_completed', ['source_id' => $replacement->id]);
        }

        return $replacement->refresh();
    }

    public function cancelReplacement(InventoryReplacement $replacement): InventoryReplacement
    {
        $replacement->forceFill(['status' => 'cancelled'])->save();

        return $replacement->refresh();
    }
}
