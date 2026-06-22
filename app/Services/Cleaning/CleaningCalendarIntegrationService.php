<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;
use App\Models\CleaningTaskIssue;
use App\Models\InspectionTask;
use App\Models\PlaceReadinessCheck;
use App\Models\SleepingPlaceCalendarDay;

class CleaningCalendarIntegrationService
{
    public function blockCalendarForCleaning(CleaningTask $task): void
    {
        if (! $task->sleeping_place_id || ! $task->scheduled_date) {
            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $task->sleeping_place_id,
                'date' => $task->scheduled_date->toDateString(),
            ],
            [
                'status' => 'cleaning',
                'reason' => 'cleaning',
                'source' => 'cleaning_task',
                'source_type' => 'cleaning_task',
                'source_id' => $task->id,
                'booking_id' => $task->booking_id,
                'blocked_by_host' => false,
            ],
        );

        app(CleaningEventService::class)->record($task, 'calendar_blocked');
    }

    public function releaseCleaningBlockIfReady(CleaningTask $task): void
    {
        if (! $task->sleeping_place_id || ! $task->scheduled_date) {
            return;
        }

        if ($task->status !== 'completed' || $task->inspection_required || $task->repair_required || $task->issues_found) {
            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $task->sleeping_place_id,
                'date' => $task->scheduled_date->toDateString(),
            ],
            [
                'status' => 'available',
                'reason' => 'cleaning_completed',
                'source' => 'cleaning_task',
                'source_type' => 'cleaning_task',
                'source_id' => $task->id,
                'booking_id' => null,
                'blocked_by_host' => false,
            ],
        );
    }

    public function blockCalendarForInspection(InspectionTask $task): void
    {
        if (! $task->sleeping_place_id || ! $task->scheduled_at) {
            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $task->sleeping_place_id,
                'date' => $task->scheduled_at->toDateString(),
            ],
            [
                'status' => 'waiting_inspection',
                'reason' => 'inspection_required',
                'source' => 'inspection_task',
                'source_type' => 'inspection_task',
                'source_id' => $task->id,
                'booking_id' => $task->booking_id,
                'blocked_by_host' => false,
            ],
        );
    }

    public function releaseInspectionBlockIfPassed(InspectionTask $task): void
    {
        if (! $task->sleeping_place_id || ! $task->scheduled_at || ! $task->passed) {
            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $task->sleeping_place_id,
                'date' => $task->scheduled_at->toDateString(),
            ],
            [
                'status' => 'available',
                'reason' => 'inspection_passed',
                'source' => 'inspection_task',
                'source_type' => 'inspection_task',
                'source_id' => $task->id,
                'booking_id' => null,
                'blocked_by_host' => false,
            ],
        );
    }

    public function blockCalendarForIssue(CleaningTaskIssue $issue): void
    {
        $task = $issue->cleaningTask;

        if (! $issue->sleeping_place_id || ! $task?->scheduled_date) {
            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $issue->sleeping_place_id,
                'date' => $task->scheduled_date->toDateString(),
            ],
            [
                'status' => in_array($issue->issue_type, ['needs_repair', 'damage_found', 'safety_issue_found'], true) ? 'repair' : 'cleaning',
                'reason' => $issue->issue_type,
                'source' => 'cleaning_issue',
                'source_type' => 'cleaning_task_issue',
                'source_id' => $issue->id,
                'booking_id' => $issue->booking_id,
                'blocked_by_host' => false,
            ],
        );
    }

    public function syncAvailabilityAfterReadiness(PlaceReadinessCheck $check): void
    {
        if (! $check->calendar_available && $check->status !== 'ready') {
            return;
        }

        $date = $check->target_check_in_at?->toDateString();

        if (! $date) {
            return;
        }

        SleepingPlaceCalendarDay::query()->updateOrCreate(
            [
                'sleeping_place_id' => $check->sleeping_place_id,
                'date' => $date,
            ],
            [
                'status' => 'available',
                'reason' => 'place_ready',
                'source' => 'place_readiness',
                'source_type' => 'place_readiness_check',
                'source_id' => $check->id,
                'booking_id' => null,
                'blocked_by_host' => false,
            ],
        );
    }
}
