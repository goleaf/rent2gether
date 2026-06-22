<?php

namespace App\Services\Cleaning;

use App\Models\CleaningTask;
use App\Models\CleaningTaskIssue;
use App\Models\ComplaintCase;
use App\Models\User;
use App\Services\Complaints\ComplaintNumberService;

class CleaningTaskIssueService
{
    public function reportIssue(User $user, CleaningTask $task, array $data): CleaningTaskIssue
    {
        app(CleaningPrivacyService::class)->ensureCanManage($user, $task);

        $issue = CleaningTaskIssue::query()->create([
            'cleaning_task_id' => $task->id,
            'booking_id' => $task->booking_id,
            'host_user_id' => $task->host_user_id,
            'property_id' => $task->property_id,
            'room_id' => $task->room_id,
            'sleeping_place_id' => $task->sleeping_place_id,
            'issue_type' => $data['issue_type'],
            'severity' => $data['severity'] ?? 'medium',
            'status' => 'reported',
            'description' => $data['description'] ?? null,
            'creates_maintenance_request' => (bool) ($data['creates_maintenance_request'] ?? false),
            'creates_deposit_review' => (bool) ($data['creates_deposit_review'] ?? false),
            'creates_complaint' => (bool) ($data['creates_complaint'] ?? false),
            'blocks_calendar' => (bool) ($data['blocks_calendar'] ?? false),
        ]);

        $task->forceFill([
            'issues_found' => true,
            'repair_required' => $task->repair_required || in_array($issue->issue_type, ['needs_repair', 'damage_found', 'safety_issue_found'], true),
            'deposit_review_required' => $task->deposit_review_required || $issue->creates_deposit_review,
            'damage_found' => $task->damage_found || in_array($issue->issue_type, ['damage_found', 'lost_inventory_found'], true),
            'extra_dirt_found' => $task->extra_dirt_found || in_array($issue->issue_type, ['extra_dirt_found', 'needs_deep_cleaning'], true),
            'forgotten_items_found' => $task->forgotten_items_found || $issue->issue_type === 'forgotten_items_found',
        ])->save();

        app(CleaningEventService::class)->record($task->refresh(), 'issue_found', [
            'user_id' => $user->id,
            'issue_type' => $issue->issue_type,
        ]);

        return $issue->refresh();
    }

    public function createMaintenanceIfNeeded(CleaningTaskIssue $issue): mixed
    {
        if (! $issue->creates_maintenance_request && ! in_array($issue->issue_type, ['needs_repair', 'damage_found', 'safety_issue_found'], true)) {
            return null;
        }

        $issue->forceFill([
            'maintenance_request_id' => $issue->maintenance_request_id ?: $issue->id,
            'status' => 'maintenance_created',
        ])->save();

        $issue->cleaningTask?->forceFill(['repair_required' => true])->save();

        return (object) ['id' => $issue->maintenance_request_id, 'source' => 'cleaning_issue'];
    }

    public function createDepositReviewIfNeeded(CleaningTaskIssue $issue): mixed
    {
        if (! $issue->creates_deposit_review) {
            return null;
        }

        $issue->forceFill([
            'booking_deposit_case_id' => $issue->booking_deposit_case_id ?: $issue->id,
            'status' => 'deposit_review_created',
        ])->save();

        $issue->cleaningTask?->forceFill(['deposit_review_required' => true])->save();

        return (object) ['id' => $issue->booking_deposit_case_id, 'source' => 'cleaning_issue'];
    }

    public function createComplaintIfNeeded(CleaningTaskIssue $issue): mixed
    {
        if (! $issue->creates_complaint || $issue->complaint_case_id) {
            return $issue->complaintCase;
        }

        $task = $issue->cleaningTask;
        $booking = $task?->booking;

        $complaint = ComplaintCase::query()->create([
            'complaint_number' => app(ComplaintNumberService::class)->generate(),
            'booking_id' => $issue->booking_id,
            'guest_user_id' => $booking?->guest_user_id,
            'host_user_id' => $issue->host_user_id,
            'reporter_user_id' => $issue->host_user_id,
            'against_user_id' => $booking?->guest_user_id,
            'property_id' => $issue->property_id,
            'room_id' => $issue->room_id,
            'sleeping_place_id' => $issue->sleeping_place_id,
            'source_type' => 'cleaning_issue',
            'source_id' => $issue->id,
            'submitted_by_type' => 'host',
            'against_type' => 'guest',
            'complaint_type' => $issue->issue_type === 'extra_dirt_found' ? 'dirty_sleeping_place' : 'property_damage',
            'severity' => $issue->severity,
            'status' => 'submitted',
            'description' => $issue->description ?: trans('cleaning.messages.issue_created_complaint'),
            'desired_resolution_type' => 'repair',
        ]);

        $issue->forceFill([
            'complaint_case_id' => $complaint->id,
            'status' => 'complaint_created',
        ])->save();

        return $complaint->refresh();
    }

    public function blockCalendarIfNeeded(CleaningTaskIssue $issue): void
    {
        if (! $issue->blocks_calendar) {
            return;
        }

        app(CleaningCalendarIntegrationService::class)->blockCalendarForIssue($issue);
    }

    public function markResolved(User $host, CleaningTaskIssue $issue): CleaningTaskIssue
    {
        app(CleaningPrivacyService::class)->ensureCanManage($host, $issue->cleaningTask);

        $issue->forceFill([
            'status' => 'resolved',
            'resolved_at' => now(),
        ])->save();

        return $issue->refresh();
    }
}
