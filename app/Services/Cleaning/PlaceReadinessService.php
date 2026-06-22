<?php

namespace App\Services\Cleaning;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\CleaningTask;
use App\Models\InspectionTask;
use App\Models\PlaceReadinessCheck;
use App\Models\SleepingPlace;
use Illuminate\Support\Collection;

class PlaceReadinessService
{
    public function __construct(
        private readonly PlaceReadinessNumberService $numbers,
    ) {}

    public function createForNextCheckIn(Booking $booking): PlaceReadinessCheck
    {
        return PlaceReadinessCheck::query()->firstOrCreate(
            [
                'booking_id' => $booking->id,
                'check_reason' => 'before_check_in',
            ],
            [
                'readiness_number' => $this->numbers->generate(),
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'host_user_id' => $booking->host_user_id,
                'status' => 'checking',
                'target_check_in_at' => $this->targetCheckInAt($booking),
                'repair_completed' => true,
                'inventory_ready' => true,
                'deposit_review_not_blocking' => true,
                'complaint_not_blocking' => true,
            ],
        )->refresh();
    }

    public function createAfterCheckout(BookingCheckOut $checkOut): PlaceReadinessCheck
    {
        return PlaceReadinessCheck::query()->create([
            'readiness_number' => $this->numbers->generate(),
            'booking_id' => $checkOut->booking_id,
            'property_id' => $checkOut->property_id,
            'room_id' => $checkOut->room_id,
            'sleeping_place_id' => $checkOut->sleeping_place_id,
            'host_user_id' => $checkOut->host_user_id,
            'status' => 'checking',
            'check_reason' => 'after_checkout',
            'checkout_completed' => (bool) $checkOut->completed_at,
            'repair_completed' => ! $checkOut->repair_required,
            'inventory_ready' => ! $checkOut->has_inventory_issue,
        ]);
    }

    public function createAfterCleaning(CleaningTask $task): PlaceReadinessCheck
    {
        return PlaceReadinessCheck::query()->create([
            'readiness_number' => $this->numbers->generate(),
            'booking_id' => $task->booking_id,
            'property_id' => $task->property_id,
            'room_id' => $task->room_id,
            'sleeping_place_id' => $task->sleeping_place_id,
            'host_user_id' => $task->host_user_id,
            'status' => 'checking',
            'check_reason' => 'after_cleaning',
            'cleaning_completed' => $task->status === 'completed',
            'repair_completed' => ! $task->repair_required,
            'inventory_ready' => ! $task->inventory_issue_found,
        ]);
    }

    public function createAfterInspection(InspectionTask $task): PlaceReadinessCheck
    {
        return PlaceReadinessCheck::query()->create([
            'readiness_number' => $this->numbers->generate(),
            'booking_id' => $task->booking_id,
            'property_id' => $task->property_id,
            'room_id' => $task->room_id,
            'sleeping_place_id' => $task->sleeping_place_id,
            'host_user_id' => $task->host_user_id,
            'status' => 'checking',
            'check_reason' => 'after_inspection',
            'inspection_completed' => $task->passed,
            'repair_completed' => ! $task->repair_required,
            'inventory_ready' => true,
        ]);
    }

    public function checkReadiness(PlaceReadinessCheck $check): PlaceReadinessCheck
    {
        $booking = $check->booking;
        $latestCleaning = $this->latestCleaning($check);
        $latestInspection = $this->latestInspection($check);
        $calendarDay = $check->target_check_in_at
            ? $check->sleepingPlace?->calendarDays()->whereDate('date', $check->target_check_in_at->toDateString())->first()
            : null;

        $checkoutCompleted = (bool) ($booking?->checked_out_at || $booking?->checkOut?->completed_at || $check->checkout_completed);
        $cleaningCompleted = (bool) ($latestCleaning?->status === 'completed' || $check->cleaning_completed);
        $inspectionRequired = (bool) ($latestCleaning?->inspection_required || $latestInspection);
        $inspectionCompleted = ! $inspectionRequired || (bool) ($latestInspection?->passed || $check->inspection_completed);
        $repairCompleted = ! (bool) ($latestCleaning?->repair_required || $latestInspection?->repair_required) && $check->repair_completed;
        $inventoryReady = ! (bool) ($latestCleaning?->inventory_issue_found) || $check->inventory_ready;
        $accessReady = (bool) ($booking?->check_in_instruction_available || $check->access_ready);
        $calendarAvailable = ! $calendarDay || in_array($calendarDay->status, ['available', 'booked'], true);

        $status = 'ready';
        $blockingReason = null;

        if (! $repairCompleted) {
            $status = 'waiting_repair';
            $blockingReason = 'repair_required';
        } elseif (! $cleaningCompleted) {
            $status = 'waiting_cleaning';
            $blockingReason = 'cleaning_required';
        } elseif (! $inspectionCompleted) {
            $status = 'waiting_inspection';
            $blockingReason = 'inspection_required';
        } elseif (! $inventoryReady) {
            $status = 'waiting_inventory';
            $blockingReason = 'inventory_not_ready';
        } elseif (! $accessReady) {
            $status = 'waiting_access_setup';
            $blockingReason = 'access_not_ready';
        } elseif (! $check->complaint_not_blocking || ! $check->deposit_review_not_blocking || ! $calendarAvailable) {
            $status = 'blocked';
            $blockingReason = ! $check->complaint_not_blocking ? 'complaint_blocks_place' : 'calendar_blocked';
        }

        $check->forceFill([
            'status' => $status,
            'checkout_completed' => $checkoutCompleted,
            'cleaning_completed' => $cleaningCompleted,
            'inspection_completed' => $inspectionCompleted,
            'repair_completed' => $repairCompleted,
            'inventory_ready' => $inventoryReady,
            'access_ready' => $accessReady,
            'calendar_available' => $calendarAvailable,
            'blocking_reason_key' => $blockingReason,
            'ready_at' => $status === 'ready' ? now() : null,
        ])->save();

        app(CleaningCalendarIntegrationService::class)->syncAvailabilityAfterReadiness($check->refresh());

        return $check->refresh();
    }

    public function markReady(PlaceReadinessCheck $check): PlaceReadinessCheck
    {
        $check->forceFill([
            'status' => 'ready',
            'ready_at' => now(),
            'calendar_available' => true,
        ])->save();

        app(CleaningCalendarIntegrationService::class)->syncAvailabilityAfterReadiness($check->refresh());

        return $check->refresh();
    }

    public function markNotReady(PlaceReadinessCheck $check, string $reason): PlaceReadinessCheck
    {
        $check->forceFill([
            'status' => 'not_ready',
            'blocking_reason_key' => $reason,
        ])->save();

        return $check->refresh();
    }

    public function getBlockingReasons(SleepingPlace $place): Collection
    {
        $reasons = collect();

        if ($place->cleaningTasks()->whereNotIn('status', ['completed', 'closed', 'cancelled'])->exists()) {
            $reasons->push('cleaning_required');
        }

        if ($place->inspectionTasks()->whereNotIn('status', ['passed', 'passed_with_notes', 'closed', 'cancelled'])->exists()) {
            $reasons->push('inspection_required');
        }

        return $reasons;
    }

    private function latestCleaning(PlaceReadinessCheck $check): ?CleaningTask
    {
        return CleaningTask::query()
            ->where('sleeping_place_id', $check->sleeping_place_id)
            ->when($check->booking_id, fn ($query) => $query->where('booking_id', $check->booking_id))
            ->latest('id')
            ->first();
    }

    private function latestInspection(PlaceReadinessCheck $check): ?InspectionTask
    {
        return InspectionTask::query()
            ->where('sleeping_place_id', $check->sleeping_place_id)
            ->when($check->booking_id, fn ($query) => $query->where('booking_id', $check->booking_id))
            ->latest('id')
            ->first();
    }

    private function targetCheckInAt(Booking $booking): ?string
    {
        if (! $booking->check_in_date) {
            return null;
        }

        $time = $booking->check_in_time ? $booking->check_in_time->format('H:i') : '17:00';

        return $booking->check_in_date->toDateString().' '.$time;
    }
}
