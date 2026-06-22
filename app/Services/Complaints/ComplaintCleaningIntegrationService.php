<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;
use App\Models\HostCleaningTask;

class ComplaintCleaningIntegrationService
{
    public function __construct(
        private readonly ComplaintActionService $actions,
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
    ) {}

    public function createCleaningFromComplaint(ComplaintCase $case): HostCleaningTask
    {
        $task = HostCleaningTask::query()->create([
            'user_id' => $case->host_user_id,
            'property_id' => $case->property_id,
            'room_id' => $case->room_id,
            'sleeping_place_id' => $case->sleeping_place_id,
            'booking_id' => $case->booking_id,
            'booking_check_out_id' => $case->booking_check_out_id,
            'cleaning_type' => 'after_complaint',
            'status' => 'planned',
            'priority' => in_array($case->severity, ['urgent', 'emergency'], true) ? 'urgent' : 'normal',
            'scheduled_date' => now()->toDateString(),
            'due_at' => now()->addHours(2),
            'reason' => 'complaint',
            'note' => $case->description,
            'after_photos_required' => true,
        ]);

        $case->forceFill([
            'cleaning_task_id' => $task->id,
            'resolution_type' => 'cleaning',
            'resolution_status' => 'in_progress',
        ])->save();

        $this->actions->createAction($case->fresh(), 'create_cleaning', [
            'status' => 'completed',
            'source_type' => 'host_cleaning_task',
            'source_id' => $task->id,
            'completed_at' => now(),
        ]);
        $this->statuses->transition($case->fresh(), 'cleaning_created');
        $this->events->record($case->fresh(), 'cleaning_created', ['cleaning_task_id' => $task->id]);

        return $task->fresh();
    }

    public function markCleaningResolvedComplaint(ComplaintCase $case): void
    {
        $this->statuses->transition($case, 'resolved');
        $this->events->record($case->fresh(), 'complaint_resolved', ['resolution_type' => 'cleaning']);
    }
}
