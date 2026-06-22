<?php

namespace App\Services\Inventory;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckOut;
use App\Models\BookingRelocation;
use App\Models\CleaningTask;
use App\Models\InspectionTask;
use App\Models\InventoryCheck;
use App\Models\User;

class InventoryCheckService
{
    public function __construct(
        private readonly InventoryNumberService $numbers,
    ) {}

    public function createForCheckIn(BookingCheckIn $checkIn): InventoryCheck
    {
        return $this->createFromContext([
            'booking_id' => $checkIn->booking_id,
            'booking_check_in_id' => $checkIn->id,
            'host_user_id' => $checkIn->host_user_id,
            'property_id' => $checkIn->property_id,
            'room_id' => $checkIn->room_id,
            'sleeping_place_id' => $checkIn->sleeping_place_id,
            'check_type' => 'check_in_issue',
        ]);
    }

    public function createForCheckOut(BookingCheckOut $checkOut): InventoryCheck
    {
        return $this->createFromContext([
            'booking_id' => $checkOut->booking_id,
            'booking_check_out_id' => $checkOut->id,
            'host_user_id' => $checkOut->host_user_id,
            'property_id' => $checkOut->property_id,
            'room_id' => $checkOut->room_id,
            'sleeping_place_id' => $checkOut->sleeping_place_id,
            'check_type' => 'check_out_return',
        ]);
    }

    public function createForCleaning(CleaningTask $task): InventoryCheck
    {
        return $this->createFromContext([
            'booking_id' => $task->booking_id,
            'cleaning_task_id' => $task->id,
            'host_user_id' => $task->host_user_id,
            'property_id' => $task->property_id,
            'room_id' => $task->room_id,
            'sleeping_place_id' => $task->sleeping_place_id,
            'check_type' => 'cleaning_check',
        ]);
    }

    public function createForInspection(InspectionTask $task): InventoryCheck
    {
        return $this->createFromContext([
            'booking_id' => $task->booking_id,
            'inspection_task_id' => $task->id,
            'host_user_id' => $task->host_user_id,
            'property_id' => $task->property_id,
            'room_id' => $task->room_id,
            'sleeping_place_id' => $task->sleeping_place_id,
            'check_type' => 'inspection_check',
        ]);
    }

    public function createForDepositReview(mixed $case): InventoryCheck
    {
        return $this->createFromContext([
            'booking_id' => $case->booking_id,
            'booking_deposit_case_id' => $case->id,
            'host_user_id' => $case->host_user_id,
            'property_id' => $case->property_id,
            'room_id' => $case->room_id,
            'sleeping_place_id' => $case->sleeping_place_id,
            'check_type' => 'deposit_review',
        ]);
    }

    public function createForRelocation(BookingRelocation $relocation): InventoryCheck
    {
        return $this->createFromContext([
            'booking_id' => $relocation->original_booking_id,
            'host_user_id' => $relocation->host_user_id,
            'property_id' => $relocation->current_property_id,
            'room_id' => $relocation->current_room_id,
            'sleeping_place_id' => $relocation->current_sleeping_place_id,
            'check_type' => 'relocation_transfer',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createManual(User $host, array $data): InventoryCheck
    {
        return $this->createFromContext(array_merge($data, [
            'host_user_id' => $host->id,
            'check_type' => $data['check_type'] ?? 'manual',
        ]));
    }

    public function markInProgress(InventoryCheck $check): InventoryCheck
    {
        $check->forceFill(['status' => 'in_progress'])->save();

        return $check->refresh();
    }

    public function markCompleted(InventoryCheck $check): InventoryCheck
    {
        $check->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
            'items_expected_count' => $check->items()->count(),
            'items_checked_count' => $check->items()->where(function ($query): void {
                $query->where('is_present', true)->orWhere('is_returned', true);
            })->count(),
            'items_missing_count' => $check->items()->where('missing', true)->count(),
            'items_damaged_count' => $check->items()->where('damaged', true)->count(),
            'issues_found' => $check->items()->where(function ($query): void {
                $query->where('missing', true)->orWhere('damaged', true)->orWhere('needs_repair', true);
            })->exists(),
        ])->save();

        return $check->refresh();
    }

    public function markCompletedWithIssues(InventoryCheck $check): InventoryCheck
    {
        $this->markCompleted($check);
        $check->forceFill(['status' => 'completed_with_issues', 'issues_found' => true])->save();

        return $check->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createFromContext(array $data): InventoryCheck
    {
        return InventoryCheck::query()->create(array_merge([
            'inventory_check_number' => $this->numbers->generateCheckNumber(),
            'status' => 'draft',
            'items_expected_count' => 0,
            'items_checked_count' => 0,
            'items_missing_count' => 0,
            'items_damaged_count' => 0,
            'issues_found' => false,
        ], $data))->refresh();
    }
}
