<?php

namespace App\Services\Inventory;

use App\Models\Booking;
use App\Models\BookingInventoryAssignment;
use App\Models\CleaningTaskIssue;
use App\Models\InventoryCheckItem;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use App\Models\User;

class InventoryIssueService
{
    public function __construct(
        private readonly InventoryNumberService $numbers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createIssue(User $user, InventoryItem $item, array $data): InventoryIssue
    {
        $booking = isset($data['booking_id'])
            ? Booking::query()->find($data['booking_id'])
            : null;

        $issue = InventoryIssue::query()->create([
            'inventory_issue_number' => $data['inventory_issue_number'] ?? $this->numbers->generateIssueNumber(),
            'inventory_item_id' => $item->id,
            'inventory_item_unit_id' => $data['inventory_item_unit_id'] ?? null,
            'booking_id' => $data['booking_id'] ?? null,
            'booking_stay_id' => $data['booking_stay_id'] ?? null,
            'booking_check_in_id' => $data['booking_check_in_id'] ?? null,
            'booking_check_out_id' => $data['booking_check_out_id'] ?? null,
            'cleaning_task_id' => $data['cleaning_task_id'] ?? null,
            'inspection_task_id' => $data['inspection_task_id'] ?? null,
            'maintenance_request_id' => $data['maintenance_request_id'] ?? null,
            'booking_deposit_case_id' => $data['booking_deposit_case_id'] ?? null,
            'complaint_case_id' => $data['complaint_case_id'] ?? null,
            'reported_by_user_id' => $user->id,
            'host_user_id' => $item->host_user_id,
            'guest_user_id' => $data['guest_user_id'] ?? $booking?->guest_user_id,
            'property_id' => $item->property_id,
            'room_id' => $item->room_id,
            'sleeping_place_id' => $item->sleeping_place_id,
            'issue_type' => $data['issue_type'],
            'severity' => $data['severity'] ?? 'medium',
            'status' => $data['status'] ?? 'reported',
            'description' => $data['description'] ?? null,
            'quantity_affected' => $data['quantity_affected'] ?? 1,
            'replacement_cost_amount' => $data['replacement_cost_amount'] ?? $item->estimated_replacement_cost_amount,
            'deduction_suggested_amount' => $data['deduction_suggested_amount'] ?? null,
            'currency' => $data['currency'] ?? $item->currency,
            'guest_responsibility_status' => $data['guest_responsibility_status'] ?? 'unknown',
        ]);

        app(InventoryEventService::class)->recordForIssue($issue, 'issue_created', ['user_id' => $user->id]);

        return $issue->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromAssignment(BookingInventoryAssignment $assignment, string $issueType, array $data = []): InventoryIssue
    {
        $item = $assignment->inventoryItem;
        $reporter = $assignment->host;

        return $this->createIssue($reporter, $item, array_merge($data, [
            'booking_id' => $assignment->booking_id,
            'booking_stay_id' => $assignment->booking_stay_id,
            'booking_check_in_id' => $assignment->booking_check_in_id,
            'booking_check_out_id' => $assignment->booking_check_out_id,
            'inventory_item_unit_id' => $assignment->inventory_item_unit_id,
            'guest_user_id' => $assignment->guest_user_id,
            'issue_type' => $issueType,
        ]));
    }

    public function createFromCheckItem(InventoryCheckItem $checkItem, string $issueType): InventoryIssue
    {
        $check = $checkItem->inventoryCheck;

        return $this->createIssue($check->host, $checkItem->inventoryItem, [
            'booking_id' => $check->booking_id,
            'booking_check_in_id' => $check->booking_check_in_id,
            'booking_check_out_id' => $check->booking_check_out_id,
            'cleaning_task_id' => $check->cleaning_task_id,
            'inspection_task_id' => $check->inspection_task_id,
            'inventory_item_unit_id' => $checkItem->inventory_item_unit_id,
            'issue_type' => $issueType,
            'description' => $checkItem->note,
        ]);
    }

    public function createFromCleaningIssue(CleaningTaskIssue $issue): InventoryIssue
    {
        $item = InventoryItem::query()
            ->where('sleeping_place_id', $issue->sleeping_place_id)
            ->where('is_required_for_readiness', true)
            ->firstOrFail();

        return $this->createIssue($issue->host, $item, [
            'booking_id' => $issue->booking_id,
            'cleaning_task_id' => $issue->cleaning_task_id,
            'issue_type' => $issue->issue_type === 'damage_found' ? 'damaged' : 'other',
            'description' => $issue->description,
        ]);
    }

    public function createFromMaintenance(mixed $request): InventoryIssue
    {
        $item = InventoryItem::query()->findOrFail($request->inventory_item_id);

        return $this->createIssue($item->host, $item, [
            'maintenance_request_id' => $request->id,
            'issue_type' => 'needs_repair',
            'description' => $request->description ?? null,
        ]);
    }

    public function markGuestResponsibilityUnknown(InventoryIssue $issue): InventoryIssue
    {
        return $this->responsibility($issue, 'unknown');
    }

    public function markPossiblyGuestFault(InventoryIssue $issue): InventoryIssue
    {
        return $this->responsibility($issue, 'possibly_guest_fault');
    }

    public function markGuestAccepted(InventoryIssue $issue): InventoryIssue
    {
        return $this->responsibility($issue, 'guest_accepted');
    }

    public function markGuestDisputed(InventoryIssue $issue): InventoryIssue
    {
        return $this->responsibility($issue, 'guest_disputed');
    }

    public function markConfirmedGuestFault(InventoryIssue $issue): InventoryIssue
    {
        return $this->responsibility($issue, 'confirmed_guest_fault');
    }

    public function markResolved(InventoryIssue $issue): InventoryIssue
    {
        $issue->forceFill(['status' => 'resolved', 'resolved_at' => now()])->save();

        return $issue->refresh();
    }

    public function closeIssue(InventoryIssue $issue): InventoryIssue
    {
        $issue->forceFill(['status' => 'closed', 'closed_at' => now()])->save();

        return $issue->refresh();
    }

    private function responsibility(InventoryIssue $issue, string $status): InventoryIssue
    {
        $issue->forceFill(['guest_responsibility_status' => $status])->save();

        return $issue->refresh();
    }
}
