<?php

namespace App\Services\Inventory;

use App\Models\BookingInventoryAssignment;
use App\Models\InventoryIssue;
use App\Models\InventoryIssueMedia;
use App\Models\InventoryItem;
use App\Models\User;

class InventoryPrivacyService
{
    public function canHostView(User $host, InventoryItem $item): bool
    {
        return (int) $item->host_user_id === (int) $host->id;
    }

    public function canHostManage(User $host, InventoryItem $item): bool
    {
        return $this->canHostView($host, $item);
    }

    public function canGuestViewAssignment(User $guest, BookingInventoryAssignment $assignment): bool
    {
        return (int) $assignment->guest_user_id === (int) $guest->id;
    }

    public function canGuestViewIssue(User $guest, InventoryIssue $issue): bool
    {
        return (int) $issue->guest_user_id === (int) $guest->id;
    }

    public function canViewIssueMedia(User $user, InventoryIssueMedia $media): bool
    {
        if ($media->visibility === 'internal' || $media->visibility === 'future_review_only') {
            return false;
        }

        $issue = $media->inventoryIssue;

        return match ($media->visibility) {
            'host_only' => (int) $issue->host_user_id === (int) $user->id,
            'guest_only' => (int) $issue->guest_user_id === (int) $user->id,
            default => (int) $issue->host_user_id === (int) $user->id || (int) $issue->guest_user_id === (int) $user->id,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function filterItemForGuest(User $guest, InventoryItem $item): array
    {
        return [
            'id' => $item->id,
            'inventory_number' => $item->inventory_number,
            'name' => $item->name,
            'description' => $item->description,
            'item_type' => $item->item_type,
            'inventory_scope' => $item->inventory_scope,
            'status' => $item->status,
            'condition_status' => $item->condition_status,
            'is_returnable' => $item->is_returnable,
            'is_guest_visible' => $item->is_guest_visible,
            'is_promised_in_listing' => $item->is_promised_in_listing,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterItemForHost(User $host, InventoryItem $item): array
    {
        if (! $this->canHostView($host, $item)) {
            return [];
        }

        return $item->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function filterAssignmentForGuest(User $guest, BookingInventoryAssignment $assignment): array
    {
        if (! $this->canGuestViewAssignment($guest, $assignment)) {
            return [];
        }

        return [
            'id' => $assignment->id,
            'assignment_number' => $assignment->assignment_number,
            'inventory_item_id' => $assignment->inventory_item_id,
            'item_name' => $assignment->inventoryItem?->name,
            'assignment_type' => $assignment->assignment_type,
            'status' => $assignment->status,
            'expected_return' => $assignment->expected_return,
            'expected_return_at' => $assignment->expected_return_at,
            'issued_at' => $assignment->issued_at,
            'returned_at' => $assignment->returned_at,
        ];
    }
}
