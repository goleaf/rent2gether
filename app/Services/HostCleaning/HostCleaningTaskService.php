<?php

namespace App\Services\HostCleaning;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\Complaint;
use App\Models\HostCleaningTask;
use App\Models\Property;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HostCleaningTaskService
{
    public function __construct(
        private readonly HostCleaningChecklistService $checklists,
        private readonly HostCleaningCalendarService $calendar,
        private readonly HostCleaningReadinessService $readiness,
    ) {}

    public function createTask(User $host, array $data): HostCleaningTask
    {
        $validated = Validator::make($data, [
            'property_id' => ['required', 'integer'],
            'room_id' => ['nullable', 'integer'],
            'sleeping_place_id' => ['nullable', 'integer'],
            'booking_id' => ['nullable', 'integer'],
            'booking_check_out_id' => ['nullable', 'integer'],
            'cleaning_type' => ['required', 'string', 'max:80'],
            'reason' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:80'],
            'priority' => ['nullable', 'string', 'max:40'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_time' => ['nullable', 'string', 'max:20'],
            'due_at' => ['nullable', 'date'],
            'host_note' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'before_photos_required' => ['nullable', 'boolean'],
            'after_photos_required' => ['nullable', 'boolean'],
        ])->validate();

        $this->authorizeProperty($host, (int) $validated['property_id']);

        $task = HostCleaningTask::query()->create([
            'user_id' => $host->id,
            'property_id' => $validated['property_id'],
            'room_id' => $validated['room_id'] ?? null,
            'sleeping_place_id' => $validated['sleeping_place_id'] ?? null,
            'booking_id' => $validated['booking_id'] ?? null,
            'booking_check_out_id' => $validated['booking_check_out_id'] ?? null,
            'cleaning_type' => $validated['cleaning_type'],
            'reason' => $validated['reason'] ?? null,
            'status' => $validated['status'] ?? 'planned',
            'priority' => $validated['priority'] ?? 'normal',
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'scheduled_time' => $validated['scheduled_time'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'host_note' => $validated['host_note'] ?? null,
            'note' => $validated['note'] ?? null,
            'before_photos_required' => (bool) ($validated['before_photos_required'] ?? false),
            'after_photos_required' => (bool) ($validated['after_photos_required'] ?? true),
        ]);

        $this->checklists->createDefaultChecklist($task);
        $this->calendar->blockCalendarForCleaning($task);

        return $task->refresh();
    }

    public function createAfterCheckout(BookingCheckOut $checkOut): HostCleaningTask
    {
        $task = HostCleaningTask::query()->firstOrCreate(
            [
                'booking_check_out_id' => $checkOut->id,
                'cleaning_type' => 'after_check_out',
            ],
            [
                'user_id' => $checkOut->host_user_id,
                'property_id' => $checkOut->property_id,
                'room_id' => $checkOut->room_id,
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'booking_id' => $checkOut->booking_id,
                'status' => 'needed',
                'priority' => 'normal',
                'scheduled_date' => $checkOut->check_out_date,
                'scheduled_time' => $checkOut->planned_check_out_time,
                'due_at' => $this->dueAt($checkOut->check_out_date, $checkOut->planned_check_out_time),
                'reason' => 'after_checkout',
                'after_photos_required' => true,
            ],
        );

        $task->forceFill([
            'booking_id' => $task->booking_id ?: $checkOut->booking_id,
            'property_id' => $task->property_id ?: $checkOut->property_id,
            'room_id' => $task->room_id ?: $checkOut->room_id,
            'sleeping_place_id' => $task->sleeping_place_id ?: $checkOut->sleeping_place_id,
            'status' => in_array($task->status, ['done', 'cancelled', 'skipped'], true) ? $task->status : 'needed',
        ])->save();

        $this->checklists->createDefaultChecklist($task);
        $this->calendar->blockCalendarForCleaning($task->refresh());

        return $task->refresh();
    }

    public function createBeforeCheckIn(Booking $booking): HostCleaningTask
    {
        return $this->createTask($booking->host, [
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'booking_id' => $booking->id,
            'cleaning_type' => 'before_check_in',
            'reason' => 'before_check_in',
            'status' => 'planned',
            'priority' => 'high',
            'scheduled_date' => $booking->check_in_date,
            'scheduled_time' => $booking->check_in_time,
        ]);
    }

    public function createAfterComplaint(Complaint $complaint): HostCleaningTask
    {
        $property = Property::query()->findOrFail($complaint->property_id);

        return $this->createTask($property->host, [
            'property_id' => $property->id,
            'room_id' => $complaint->room_id,
            'sleeping_place_id' => $complaint->sleeping_place_id,
            'booking_id' => $complaint->booking_id,
            'cleaning_type' => 'after_complaint',
            'reason' => 'after_complaint',
            'status' => 'needed',
            'priority' => 'urgent',
            'scheduled_date' => now()->toDateString(),
        ]);
    }

    public function createAfterRepair(User $host, array $data): HostCleaningTask
    {
        return $this->createTask($host, [
            ...$data,
            'cleaning_type' => $data['cleaning_type'] ?? 'after_repair',
            'reason' => $data['reason'] ?? 'after_repair',
            'status' => $data['status'] ?? 'needed',
        ]);
    }

    public function assignResponsible(User $host, HostCleaningTask $task, array $data): HostCleaningTask
    {
        $this->authorizeTask($host, $task);

        $validated = Validator::make($data, [
            'assigned_to_type' => ['required', 'in:host,host_representative,external_person,future_user'],
            'assigned_to_user_id' => ['nullable', 'integer'],
            'assigned_person_name' => ['nullable', 'string', 'max:120'],
            'assigned_person_contact' => ['nullable', 'string', 'max:120'],
        ])->validate();

        $task->forceFill([
            'assigned_to_type' => $validated['assigned_to_type'],
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'assigned_person_name' => $validated['assigned_person_name'] ?? null,
            'assigned_person_contact' => $validated['assigned_person_contact'] ?? null,
            'status' => $task->status === 'planned' ? 'assigned' : $task->status,
        ])->save();

        return $task->refresh();
    }

    public function start(User $host, HostCleaningTask $task): HostCleaningTask
    {
        $this->authorizeTask($host, $task);

        $task->forceFill([
            'status' => 'in_progress',
            'started_at' => $task->started_at ?: now(),
        ])->save();

        $this->calendar->blockCalendarForCleaning($task->refresh());

        return $task->refresh();
    }

    public function complete(User $host, HostCleaningTask $task): HostCleaningTask
    {
        $this->authorizeTask($host, $task);

        $issues = $this->readiness->getBlockingIssues($task);

        if ($issues !== []) {
            throw ValidationException::withMessages([
                'cleaning' => trans('cleaning.validation.cannot_complete'),
            ]);
        }

        $hasOpenFindings = $task->findings()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->exists();
        $doneWithIssues = $hasOpenFindings
            || $task->has_damage_found
            || $task->has_forgotten_items
            || $task->has_extra_dirty
            || $task->needs_repair
            || $task->needs_repeat_cleaning;

        $task->forceFill([
            'status' => $doneWithIssues ? 'done_with_issues' : 'done',
            'completed_at' => now(),
            'place_ready_after_cleaning' => ! $doneWithIssues,
        ])->save();

        $fresh = $task->refresh();
        $doneWithIssues
            ? $this->calendar->syncHostCalendarEvent($fresh)
            : $this->calendar->releaseCalendarAfterCleaning($fresh);

        return $fresh;
    }

    public function cancel(User $host, HostCleaningTask $task): HostCleaningTask
    {
        $this->authorizeTask($host, $task);

        $task->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ])->save();

        $this->calendar->syncHostCalendarEvent($task->refresh());

        return $task->refresh();
    }

    public function markOverdueTasks(User $host): int
    {
        return HostCleaningTask::query()
            ->where('user_id', $host->id)
            ->whereNotIn('status', ['done', 'done_with_issues', 'cancelled', 'skipped', 'overdue'])
            ->where(function ($query): void {
                $query->where('due_at', '<', now())
                    ->orWhereDate('scheduled_date', '<', now()->toDateString());
            })
            ->update(['status' => 'overdue']);
    }

    private function authorizeTask(User $host, HostCleaningTask $task): void
    {
        if ((int) $task->user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }
    }

    private function authorizeProperty(User $host, int $propertyId): void
    {
        $allowed = Property::query()
            ->whereKey($propertyId)
            ->where(function ($query) use ($host): void {
                $query->where('host_user_id', $host->id)
                    ->orWhere('user_id', $host->id);
            })
            ->exists();

        if (! $allowed) {
            throw new AuthorizationException;
        }
    }

    private function dueAt(mixed $date, mixed $time): ?CarbonImmutable
    {
        if (! $date) {
            return null;
        }

        $dateString = $date instanceof CarbonInterface ? $date->toDateString() : CarbonImmutable::parse($date)->toDateString();

        return CarbonImmutable::parse($dateString.' '.($time ?: '14:00'));
    }
}
