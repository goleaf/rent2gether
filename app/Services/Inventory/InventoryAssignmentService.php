<?php

namespace App\Services\Inventory;

use App\Models\Booking;
use App\Models\BookingInventoryAssignment;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use App\Models\InventoryItemUnit;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class InventoryAssignmentService
{
    public function __construct(
        private readonly InventoryNumberService $numbers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function issueToGuest(User $host, Booking $booking, InventoryItem $item, array $data = []): BookingInventoryAssignment
    {
        $this->authorizeHost($host, $booking, $item);

        $assignment = BookingInventoryAssignment::query()->create([
            'assignment_number' => $data['assignment_number'] ?? $this->numbers->generateAssignmentNumber(),
            'booking_id' => $booking->id,
            'booking_stay_id' => $data['booking_stay_id'] ?? null,
            'booking_check_in_id' => $data['booking_check_in_id'] ?? null,
            'booking_check_out_id' => $data['booking_check_out_id'] ?? null,
            'booking_relocation_id' => $data['booking_relocation_id'] ?? null,
            'guest_user_id' => $booking->guest_user_id ?: $booking->guest_id,
            'host_user_id' => $host->id,
            'property_id' => $item->property_id,
            'room_id' => $item->room_id,
            'sleeping_place_id' => $item->sleeping_place_id,
            'inventory_item_id' => $item->id,
            'inventory_item_unit_id' => $data['inventory_item_unit_id'] ?? null,
            'assignment_type' => $data['assignment_type'] ?? 'issued_at_check_in',
            'status' => 'issued',
            'issued_at' => $data['issued_at'] ?? now(),
            'issued_by_user_id' => $host->id,
            'issued_by_type' => $data['issued_by_type'] ?? 'host',
            'expected_return' => (bool) ($data['expected_return'] ?? $item->is_returnable),
            'expected_return_at' => $data['expected_return_at'] ?? $booking->check_out_date,
            'condition_at_issue' => $data['condition_at_issue'] ?? $item->condition_status,
            'quantity' => $data['quantity'] ?? 1,
            'issue_note' => $data['issue_note'] ?? null,
        ]);

        $item->forceFill([
            'status' => 'issued_to_guest',
            'current_location_type' => 'guest',
            'last_issued_at' => now(),
        ])->save();

        if ($assignment->inventoryItemUnit) {
            app(InventoryItemUnitService::class)->markUnitIssued($assignment->inventoryItemUnit, $booking, $assignment->guest);
        }

        app(InventoryMovementService::class)->recordMovement($item->refresh(), 'issued_to_guest', [
            'inventory_item_unit_id' => $assignment->inventory_item_unit_id,
            'booking_id' => $booking->id,
            'booking_inventory_assignment_id' => $assignment->id,
            'to_location_type' => 'guest',
            'moved_by_user_id' => $host->id,
            'quantity' => $assignment->quantity,
        ]);
        app(InventoryEventService::class)->recordForAssignment($assignment, 'item_issued_to_guest', ['user_id' => $host->id]);

        return $assignment->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function issueUnitToGuest(User $host, Booking $booking, InventoryItemUnit $unit, array $data = []): BookingInventoryAssignment
    {
        return $this->issueToGuest($host, $booking, $unit->inventoryItem, array_merge($data, [
            'inventory_item_unit_id' => $unit->id,
        ]));
    }

    public function guestConfirmReceived(User $guest, BookingInventoryAssignment $assignment): BookingInventoryAssignment
    {
        if ((int) $assignment->guest_user_id !== (int) $guest->id) {
            throw new AuthorizationException(__('inventory.validation.guest_must_own_assignment'));
        }

        $assignment->forceFill([
            'status' => 'received_by_guest',
            'guest_confirmed_received_at' => now(),
        ])->save();
        app(InventoryEventService::class)->recordForAssignment($assignment->refresh(), 'guest_confirmed_received', ['user_id' => $guest->id]);

        return $assignment->refresh();
    }

    public function hostConfirmIssued(User $host, BookingInventoryAssignment $assignment): BookingInventoryAssignment
    {
        if ((int) $assignment->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('inventory.validation.host_must_own_assignment'));
        }

        $assignment->forceFill(['host_confirmed_issued_at' => now()])->save();

        return $assignment->refresh();
    }

    public function markReturnExpected(BookingInventoryAssignment $assignment): BookingInventoryAssignment
    {
        $assignment->forceFill(['status' => 'return_expected', 'expected_return' => true])->save();

        return $assignment->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function markReturned(User $user, BookingInventoryAssignment $assignment, array $data = []): BookingInventoryAssignment
    {
        $updates = [
            'status' => 'returned',
            'returned_at' => now(),
            'returned_to_user_id' => $user->id,
            'condition_at_return' => $data['condition_at_return'] ?? $assignment->condition_at_issue,
            'returned_condition_status' => $data['condition_at_return'] ?? $assignment->condition_at_issue,
            'return_note' => $data['return_note'] ?? $assignment->return_note,
        ];

        if ((int) $user->id === (int) $assignment->guest_user_id) {
            $updates['guest_confirmed_returned_at'] = now();
        }

        if ((int) $user->id === (int) $assignment->host_user_id) {
            $updates['host_confirmed_returned_at'] = now();
        }

        $assignment->forceFill($updates)->save();
        $assignment->inventoryItem->forceFill([
            'status' => 'available',
            'current_location_type' => 'storage',
            'last_returned_at' => now(),
        ])->save();

        if ($assignment->inventoryItemUnit) {
            app(InventoryItemUnitService::class)->markUnitReturned($assignment->inventoryItemUnit);
        }

        app(InventoryMovementService::class)->recordMovement($assignment->inventoryItem->refresh(), 'returned_by_guest', [
            'inventory_item_unit_id' => $assignment->inventory_item_unit_id,
            'booking_id' => $assignment->booking_id,
            'booking_inventory_assignment_id' => $assignment->id,
            'to_location_type' => 'storage',
            'moved_by_user_id' => $user->id,
            'quantity' => $assignment->quantity,
        ]);
        app(InventoryEventService::class)->recordForAssignment($assignment->refresh(), 'item_returned', ['user_id' => $user->id]);

        return $assignment->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function markNotReturned(User $host, BookingInventoryAssignment $assignment, array $data = []): InventoryIssue
    {
        if ((int) $assignment->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('inventory.validation.host_must_own_assignment'));
        }

        $assignment->forceFill(['status' => 'not_returned', 'issue_note' => $data['description'] ?? null])->save();
        $assignment->inventoryItem->forceFill(['status' => 'missing', 'current_location_type' => 'guest'])->save();

        if ($assignment->inventoryItemUnit) {
            app(InventoryItemUnitService::class)->markUnitLost($assignment->inventoryItemUnit);
        }

        $issue = app(InventoryIssueService::class)->createFromAssignment($assignment->refresh(), 'not_returned', $data);
        app(InventoryEventService::class)->recordForAssignment($assignment->refresh(), 'item_not_returned', ['user_id' => $host->id]);

        return $issue;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function markReturnedDamaged(User $host, BookingInventoryAssignment $assignment, array $data = []): InventoryIssue
    {
        if ((int) $assignment->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('inventory.validation.host_must_own_assignment'));
        }

        $assignment->forceFill([
            'status' => 'returned_damaged',
            'returned_at' => now(),
            'host_confirmed_returned_at' => now(),
            'condition_at_return' => 'damaged',
            'returned_condition_status' => 'damaged',
            'return_note' => $data['description'] ?? null,
        ])->save();
        $assignment->inventoryItem->forceFill(['status' => 'damaged', 'condition_status' => 'damaged'])->save();

        if ($assignment->inventoryItemUnit) {
            app(InventoryItemUnitService::class)->markUnitDamaged($assignment->inventoryItemUnit);
        }

        $issue = app(InventoryIssueService::class)->createFromAssignment($assignment->refresh(), 'damaged', $data);
        app(InventoryEventService::class)->recordForAssignment($assignment->refresh(), 'item_damaged', ['user_id' => $host->id]);

        return $issue;
    }

    public function closeAssignment(BookingInventoryAssignment $assignment): BookingInventoryAssignment
    {
        $assignment->forceFill(['status' => 'closed'])->save();

        return $assignment->refresh();
    }

    private function authorizeHost(User $host, Booking $booking, InventoryItem $item): void
    {
        if ((int) $booking->host_user_id !== (int) $host->id || (int) $item->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('inventory.validation.host_must_own_inventory'));
        }
    }
}
