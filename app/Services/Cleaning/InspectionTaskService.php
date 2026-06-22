<?php

namespace App\Services\Cleaning;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\CleaningTask;
use App\Models\ComplaintCase;
use App\Models\InspectionTask;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Pagination\CursorPaginator;

class InspectionTaskService
{
    public function __construct(
        private readonly InspectionNumberService $numbers,
        private readonly InspectionTaskItemService $items,
        private readonly CleaningCalendarIntegrationService $calendar,
    ) {}

    public function createPostCheckout(BookingCheckOut $checkOut): InspectionTask
    {
        $task = InspectionTask::query()->firstOrCreate(
            [
                'booking_check_out_id' => $checkOut->id,
                'inspection_type' => 'post_checkout',
            ],
            [
                'inspection_number' => $this->numbers->generate(),
                'booking_id' => $checkOut->booking_id,
                'booking_stay_id' => $checkOut->booking_stay_id,
                'host_user_id' => $checkOut->host_user_id,
                'property_id' => $checkOut->property_id,
                'room_id' => $checkOut->room_id,
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'inspection_scope' => $checkOut->sleeping_place_id ? 'sleeping_place' : 'room',
                'status' => 'scheduled',
                'priority' => $checkOut->repair_required ? 'urgent' : 'normal',
                'scheduled_at' => $this->dateTime($checkOut->check_out_date, $checkOut->planned_check_out_time),
            ],
        );

        $this->items->createDefaultItems($task);
        app(InspectionEventService::class)->record($task, 'inspection_created');

        return $task->refresh();
    }

    public function createPostCleaning(CleaningTask $cleaningTask): InspectionTask
    {
        $task = InspectionTask::query()->firstOrCreate(
            [
                'cleaning_task_id' => $cleaningTask->id,
                'inspection_type' => 'post_cleaning',
            ],
            [
                'inspection_number' => $this->numbers->generate(),
                'booking_id' => $cleaningTask->booking_id,
                'booking_stay_id' => $cleaningTask->booking_stay_id,
                'booking_check_in_id' => $cleaningTask->booking_check_in_id,
                'booking_check_out_id' => $cleaningTask->booking_check_out_id,
                'booking_relocation_id' => $cleaningTask->booking_relocation_id,
                'complaint_case_id' => $cleaningTask->complaint_case_id,
                'maintenance_request_id' => $cleaningTask->maintenance_request_id,
                'mismatch_report_id' => $cleaningTask->mismatch_report_id,
                'host_user_id' => $cleaningTask->host_user_id,
                'property_id' => $cleaningTask->property_id,
                'room_id' => $cleaningTask->room_id,
                'sleeping_place_id' => $cleaningTask->sleeping_place_id,
                'inspection_scope' => $cleaningTask->sleeping_place_id ? 'sleeping_place' : $cleaningTask->cleaning_scope,
                'status' => 'scheduled',
                'priority' => $cleaningTask->priority === 'same_day_turnover' ? 'same_day_turnover' : 'normal',
                'scheduled_at' => $cleaningTask->actual_completed_at ?? $cleaningTask->scheduled_end_at ?? now(),
            ],
        );

        $cleaningTask->forceFill(['inspection_required' => true])->save();
        $this->items->createDefaultItems($task);
        $this->calendar->blockCalendarForInspection($task);
        app(InspectionEventService::class)->record($task, 'inspection_created');

        return $task->refresh();
    }

    public function createPreCheckIn(Booking $booking): InspectionTask
    {
        $task = InspectionTask::query()->create([
            'inspection_number' => $this->numbers->generate(),
            'booking_id' => $booking->id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'inspection_type' => 'pre_check_in',
            'inspection_scope' => 'sleeping_place',
            'status' => 'scheduled',
            'priority' => 'high',
            'scheduled_at' => $this->dateTime($booking->check_in_date, $booking->check_in_time),
        ]);

        $this->items->createDefaultItems($task);

        return $task->refresh();
    }

    public function createPostRepair($maintenanceRequest): InspectionTask
    {
        $task = InspectionTask::query()->create([
            'inspection_number' => $this->numbers->generate(),
            'booking_id' => $maintenanceRequest->booking_id ?? null,
            'maintenance_request_id' => $maintenanceRequest->id ?? null,
            'host_user_id' => $maintenanceRequest->host_user_id,
            'property_id' => $maintenanceRequest->property_id,
            'room_id' => $maintenanceRequest->room_id ?? null,
            'sleeping_place_id' => $maintenanceRequest->sleeping_place_id ?? null,
            'inspection_type' => 'post_repair',
            'inspection_scope' => isset($maintenanceRequest->sleeping_place_id) ? 'sleeping_place' : 'room',
            'status' => 'scheduled',
            'priority' => 'high',
            'scheduled_at' => now(),
        ]);

        $this->items->createDefaultItems($task);

        return $task->refresh();
    }

    public function createAfterComplaint(ComplaintCase $case): InspectionTask
    {
        $task = InspectionTask::query()->create([
            'inspection_number' => $this->numbers->generate(),
            'booking_id' => $case->booking_id,
            'complaint_case_id' => $case->id,
            'host_user_id' => $case->host_user_id,
            'property_id' => $case->property_id,
            'room_id' => $case->room_id,
            'sleeping_place_id' => $case->sleeping_place_id,
            'inspection_type' => 'after_complaint',
            'inspection_scope' => $case->sleeping_place_id ? 'sleeping_place' : 'room',
            'status' => 'scheduled',
            'priority' => 'urgent',
            'scheduled_at' => now(),
        ]);

        $this->items->createDefaultItems($task);

        return $task->refresh();
    }

    public function createManual(User $host, array $data): InspectionTask
    {
        $task = InspectionTask::query()->create([
            ...$data,
            'inspection_number' => $this->numbers->generate(),
            'host_user_id' => $host->id,
            'status' => $data['status'] ?? 'scheduled',
            'priority' => $data['priority'] ?? 'normal',
        ]);

        $this->items->createDefaultItems($task);

        return $task->refresh();
    }

    public function markStarted(User $user, InspectionTask $task): InspectionTask
    {
        app(InspectionPrivacyService::class)->ensureCanManage($user, $task);

        $task->forceFill([
            'status' => 'in_progress',
            'actual_started_at' => $task->actual_started_at ?: now(),
        ])->save();

        app(InspectionEventService::class)->record($task->refresh(), 'inspection_started', ['user_id' => $user->id]);

        return $task->refresh();
    }

    public function markPassed(User $user, InspectionTask $task, array $data = []): InspectionTask
    {
        app(InspectionPrivacyService::class)->ensureCanManage($user, $task);

        $task->forceFill([
            'status' => 'passed',
            'passed' => true,
            'issues_found' => false,
            'checklist_completed' => app(InspectionTaskItemService::class)->isChecklistCompleted($task),
            'result_summary' => $data['result_summary'] ?? $task->result_summary,
            'actual_completed_at' => $task->actual_completed_at ?: now(),
            'completed_at' => $task->completed_at ?: now(),
        ])->save();

        app(InspectionEventService::class)->record($task->refresh(), 'inspection_passed', ['user_id' => $user->id]);
        $this->calendar->releaseInspectionBlockIfPassed($task->refresh());

        return $task->refresh();
    }

    public function markPassedWithNotes(User $user, InspectionTask $task, array $data = []): InspectionTask
    {
        $passed = $this->markPassed($user, $task, $data);

        return $passed->forceFill(['status' => 'passed_with_notes'])->save() ? $passed->refresh() : $passed;
    }

    public function markFailed(User $user, InspectionTask $task, array $data): InspectionTask
    {
        app(InspectionPrivacyService::class)->ensureCanManage($user, $task);

        $task->forceFill([
            'status' => 'failed',
            'passed' => false,
            'issues_found' => true,
            'cleaning_required' => (bool) ($data['cleaning_required'] ?? false),
            'repair_required' => (bool) ($data['repair_required'] ?? false),
            'deposit_review_required' => (bool) ($data['deposit_review_required'] ?? false),
            'calendar_block_required' => true,
            'result_summary' => $data['result_summary'] ?? $task->result_summary,
            'actual_completed_at' => $task->actual_completed_at ?: now(),
            'completed_at' => $task->completed_at ?: now(),
        ])->save();

        app(InspectionEventService::class)->record($task->refresh(), 'inspection_failed', ['user_id' => $user->id]);

        return $task->refresh();
    }

    public function cancel(User $host, InspectionTask $task, string $reason): InspectionTask
    {
        app(InspectionPrivacyService::class)->ensureCanManage($host, $task);

        $task->forceFill([
            'status' => 'cancelled',
            'host_comment' => $reason,
            'closed_at' => now(),
        ])->save();

        return $task->refresh();
    }

    public function getForHost(User $host, array $filters = []): CursorPaginator
    {
        return InspectionTask::query()
            ->select([
                'id',
                'inspection_number',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'inspection_type',
                'inspection_scope',
                'status',
                'priority',
                'scheduled_at',
                'issues_found',
                'created_at',
            ])
            ->with([
                'property:id,title',
                'room:id,title',
                'sleepingPlace:id,display_name,place_number',
            ])
            ->where('host_user_id', $host->id)
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('id')
            ->cursorPaginate(20);
    }

    private function dateTime(CarbonInterface|string|null $date, mixed $time): ?CarbonImmutable
    {
        if (! $date) {
            return null;
        }

        $dateString = $date instanceof CarbonInterface ? $date->toDateString() : (string) $date;
        $timeString = $time instanceof CarbonInterface ? $time->format('H:i') : ($time ?: '12:00');

        return CarbonImmutable::parse($dateString.' '.$timeString);
    }
}
