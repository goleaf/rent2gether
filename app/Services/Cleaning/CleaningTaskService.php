<?php

namespace App\Services\Cleaning;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingRelocation;
use App\Models\CleaningTask;
use App\Models\ComplaintCase;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CleaningTaskService
{
    public function __construct(
        private readonly CleaningNumberService $numbers,
        private readonly CleaningTaskItemService $items,
        private readonly CleaningCalendarIntegrationService $calendar,
        private readonly CleaningPolicyService $policies,
    ) {}

    public function createAfterCheckout(BookingCheckOut $checkOut): CleaningTask
    {
        $existing = CleaningTask::query()
            ->where('booking_check_out_id', $checkOut->id)
            ->where('cleaning_type', 'after_check_out')
            ->first();

        if ($existing) {
            $this->items->createDefaultItems($existing);
            $this->calendar->blockCalendarForCleaning($existing);

            return $existing->refresh();
        }

        $place = $checkOut->sleepingPlace;
        $room = $checkOut->room;
        $property = $checkOut->property;
        $policy = $this->policies->resolveForContext($property, $room, $place);
        $scheduledStart = $this->dateTime($checkOut->check_out_date, $checkOut->planned_check_out_time);

        $task = CleaningTask::query()->create([
            'cleaning_number' => $this->numbers->generate(),
            'booking_id' => $checkOut->booking_id,
            'booking_stay_id' => $checkOut->booking_stay_id,
            'booking_check_out_id' => $checkOut->id,
            'host_user_id' => $checkOut->host_user_id,
            'property_id' => $checkOut->property_id,
            'room_id' => $checkOut->room_id,
            'sleeping_place_id' => $checkOut->sleeping_place_id,
            'cleaning_type' => 'after_check_out',
            'cleaning_scope' => $this->scopeForContext($checkOut->sleeping_place_id, $checkOut->room_id),
            'status' => 'scheduled',
            'priority' => 'normal',
            'scheduled_date' => $this->dateString($checkOut->check_out_date),
            'scheduled_start_at' => $scheduledStart,
            'scheduled_end_at' => $scheduledStart?->addMinutes((int) $policy->default_cleaning_duration_minutes),
            'before_photos_required' => (bool) $policy->require_before_photos,
            'after_photos_required' => (bool) $policy->require_after_photos,
            'inspection_required' => (bool) ($policy->inspection_required_after_cleaning || $checkOut->inspection_required),
        ]);

        $this->items->createDefaultItems($task);
        $this->calendar->blockCalendarForCleaning($task);
        app(CleaningEventService::class)->record($task, 'cleaning_created');

        return $task->refresh();
    }

    public function createBeforeCheckIn(Booking $booking): CleaningTask
    {
        return $this->createManual($booking->host, [
            'booking_id' => $booking->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'cleaning_type' => 'before_check_in',
            'cleaning_scope' => 'sleeping_place',
            'priority' => 'high',
            'scheduled_date' => $this->dateString($booking->check_in_date),
            'scheduled_start_at' => $this->dateTime($booking->check_in_date, $booking->check_in_time),
        ]);
    }

    public function createAfterComplaint(ComplaintCase $case): CleaningTask
    {
        $task = $this->createManual($case->host, [
            'booking_id' => $case->booking_id,
            'booking_stay_id' => $case->booking_stay_id,
            'booking_check_in_id' => $case->booking_check_in_id,
            'booking_check_out_id' => $case->booking_check_out_id,
            'complaint_case_id' => $case->id,
            'property_id' => $case->property_id,
            'room_id' => $case->room_id,
            'sleeping_place_id' => $case->sleeping_place_id,
            'cleaning_type' => 'after_complaint',
            'cleaning_scope' => $this->scopeForContext($case->sleeping_place_id, $case->room_id),
            'priority' => 'urgent',
            'scheduled_date' => now()->toDateString(),
            'scheduled_start_at' => now(),
        ]);

        $case->forceFill(['cleaning_task_id' => $task->id])->save();

        return $task;
    }

    public function createAfterRepair($maintenanceRequest): CleaningTask
    {
        $host = User::query()->findOrFail($maintenanceRequest->host_user_id);

        return $this->createManual($host, [
            'booking_id' => $maintenanceRequest->booking_id ?? null,
            'property_id' => $maintenanceRequest->property_id,
            'room_id' => $maintenanceRequest->room_id ?? null,
            'sleeping_place_id' => $maintenanceRequest->sleeping_place_id ?? null,
            'maintenance_request_id' => $maintenanceRequest->id ?? null,
            'cleaning_type' => 'after_repair',
            'cleaning_scope' => $this->scopeForContext($maintenanceRequest->sleeping_place_id ?? null, $maintenanceRequest->room_id ?? null),
            'priority' => 'high',
            'scheduled_date' => now()->toDateString(),
            'scheduled_start_at' => now(),
        ]);
    }

    public function createAfterRelocation(BookingRelocation $relocation): CleaningTask
    {
        return $this->createManual($relocation->host, [
            'booking_id' => $relocation->booking_id,
            'booking_relocation_id' => $relocation->id,
            'property_id' => $relocation->property_id,
            'room_id' => $relocation->current_room_id ?? null,
            'sleeping_place_id' => $relocation->current_sleeping_place_id,
            'cleaning_type' => 'after_relocation',
            'cleaning_scope' => 'sleeping_place',
            'priority' => 'high',
            'scheduled_date' => $this->dateString($relocation->relocation_date),
        ]);
    }

    public function createManual(User $host, array $data): CleaningTask
    {
        $validated = Validator::make($data, [
            'booking_id' => ['nullable', 'integer'],
            'booking_stay_id' => ['nullable', 'integer'],
            'booking_check_in_id' => ['nullable', 'integer'],
            'booking_check_out_id' => ['nullable', 'integer'],
            'booking_relocation_id' => ['nullable', 'integer'],
            'complaint_case_id' => ['nullable', 'integer'],
            'maintenance_request_id' => ['nullable', 'integer'],
            'mismatch_report_id' => ['nullable', 'integer'],
            'property_id' => ['required', 'integer'],
            'room_id' => ['nullable', 'integer'],
            'sleeping_place_id' => ['nullable', 'integer'],
            'cleaning_type' => ['required', 'string', 'max:80'],
            'cleaning_scope' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:80'],
            'priority' => ['nullable', 'string', 'max:80'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_start_at' => ['nullable', 'date'],
            'scheduled_end_at' => ['nullable', 'date'],
            'before_photos_required' => ['nullable', 'boolean'],
            'after_photos_required' => ['nullable', 'boolean'],
            'inspection_required' => ['nullable', 'boolean'],
            'host_comment' => ['nullable', 'string'],
            'internal_host_note' => ['nullable', 'string'],
        ])->validate();

        $this->authorizeProperty($host, (int) $validated['property_id']);

        $property = Property::query()->findOrFail($validated['property_id']);
        $room = isset($validated['room_id']) ? Room::query()->find($validated['room_id']) : null;
        $place = isset($validated['sleeping_place_id']) ? SleepingPlace::query()->find($validated['sleeping_place_id']) : null;
        $policy = $this->policies->resolveForContext($property, $room, $place);
        $scheduledStart = isset($validated['scheduled_start_at']) ? CarbonImmutable::parse($validated['scheduled_start_at']) : null;

        $task = CleaningTask::query()->create([
            ...$validated,
            'cleaning_number' => $this->numbers->generate(),
            'host_user_id' => $host->id,
            'cleaning_scope' => $validated['cleaning_scope'] ?? $this->scopeForContext($validated['sleeping_place_id'] ?? null, $validated['room_id'] ?? null),
            'status' => $validated['status'] ?? 'scheduled',
            'priority' => $validated['priority'] ?? 'normal',
            'scheduled_start_at' => $scheduledStart,
            'scheduled_end_at' => $validated['scheduled_end_at'] ?? $scheduledStart?->addMinutes((int) $policy->default_cleaning_duration_minutes),
            'before_photos_required' => (bool) ($validated['before_photos_required'] ?? $policy->require_before_photos),
            'after_photos_required' => (bool) ($validated['after_photos_required'] ?? $policy->require_after_photos),
            'inspection_required' => (bool) ($validated['inspection_required'] ?? $policy->inspection_required_after_cleaning),
        ]);

        $this->items->createDefaultItems($task);
        $this->calendar->blockCalendarForCleaning($task);
        app(CleaningEventService::class)->record($task, 'cleaning_created');

        return $task->refresh();
    }

    public function assignResponsible(User $host, CleaningTask $task, array $data): CleaningTask
    {
        app(CleaningPrivacyService::class)->ensureCanManage($host, $task);

        $validated = Validator::make($data, [
            'responsible_type' => ['required', 'in:host,host_representative,external_person,future_user,not_assigned'],
            'responsible_user_id' => ['nullable', 'integer'],
            'responsible_name_snapshot' => ['nullable', 'string', 'max:120'],
            'responsible_contact_snapshot' => ['nullable', 'string', 'max:120'],
        ])->validate();

        $task->forceFill([
            ...$validated,
            'status' => in_array($task->status, ['draft', 'scheduled'], true) ? 'assigned' : $task->status,
        ])->save();

        app(CleaningEventService::class)->record($task->refresh(), 'cleaning_assigned', ['user_id' => $host->id]);

        return $task->refresh();
    }

    public function markStarted(User $user, CleaningTask $task): CleaningTask
    {
        app(CleaningPrivacyService::class)->ensureCanManage($user, $task);

        $task->forceFill([
            'status' => 'in_progress',
            'actual_started_at' => $task->actual_started_at ?: now(),
        ])->save();

        app(CleaningEventService::class)->record($task->refresh(), 'cleaning_started', ['user_id' => $user->id]);

        return $task->refresh();
    }

    public function markCompleted(User $user, CleaningTask $task, array $data = []): CleaningTask
    {
        app(CleaningPrivacyService::class)->ensureCanManage($user, $task);

        if ($this->items->getRequiredIncompleteItems($task)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'cleaning' => trans('cleaning.validation.incomplete_checklist'),
            ]);
        }

        if ($task->before_photos_required && ! $task->before_photos_uploaded) {
            throw ValidationException::withMessages([
                'cleaning' => trans('cleaning.validation.before_photo_required'),
            ]);
        }

        if ($task->after_photos_required && ! $task->after_photos_uploaded) {
            throw ValidationException::withMessages([
                'cleaning' => trans('cleaning.validation.after_photo_required'),
            ]);
        }

        $task->forceFill([
            'status' => 'completed',
            'checklist_completed' => true,
            'responsible_comment' => $data['responsible_comment'] ?? $task->responsible_comment,
            'host_comment' => $data['host_comment'] ?? $task->host_comment,
            'actual_completed_at' => $task->actual_completed_at ?: now(),
            'completed_at' => $task->completed_at ?: now(),
        ])->save();

        app(CleaningEventService::class)->record($task->refresh(), 'cleaning_completed', ['user_id' => $user->id]);
        $this->calendar->releaseCleaningBlockIfReady($task->refresh());

        return $task->refresh();
    }

    public function markCompletedWithIssues(User $user, CleaningTask $task, array $data): CleaningTask
    {
        app(CleaningPrivacyService::class)->ensureCanManage($user, $task);

        $task->forceFill([
            'status' => 'completed_with_issues',
            'issues_found' => true,
            'damage_found' => (bool) ($data['damage_found'] ?? $task->damage_found),
            'extra_dirt_found' => (bool) ($data['extra_dirt_found'] ?? $task->extra_dirt_found),
            'forgotten_items_found' => (bool) ($data['forgotten_items_found'] ?? $task->forgotten_items_found),
            'inventory_issue_found' => (bool) ($data['inventory_issue_found'] ?? $task->inventory_issue_found),
            'repair_required' => (bool) ($data['repair_required'] ?? $task->repair_required),
            'inspection_required' => true,
            'deposit_review_required' => (bool) ($data['deposit_review_required'] ?? $task->deposit_review_required),
            'actual_completed_at' => $task->actual_completed_at ?: now(),
            'completed_at' => $task->completed_at ?: now(),
        ])->save();

        app(CleaningEventService::class)->record($task->refresh(), 'issue_found', ['user_id' => $user->id]);

        return $task->refresh();
    }

    public function cancel(User $host, CleaningTask $task, string $reason): CleaningTask
    {
        app(CleaningPrivacyService::class)->ensureCanManage($host, $task);

        $task->forceFill([
            'status' => 'cancelled',
            'closed_at' => now(),
            'host_comment' => $reason,
        ])->save();

        return $task->refresh();
    }

    public function reschedule(User $host, CleaningTask $task, array $data): CleaningTask
    {
        app(CleaningPrivacyService::class)->ensureCanManage($host, $task);

        $task->forceFill([
            'status' => 'rescheduled',
            'scheduled_date' => $data['scheduled_date'] ?? $task->scheduled_date,
            'scheduled_start_at' => $data['scheduled_start_at'] ?? $task->scheduled_start_at,
            'scheduled_end_at' => $data['scheduled_end_at'] ?? $task->scheduled_end_at,
        ])->save();

        return $task->refresh();
    }

    public function getForHost(User $host, array $filters = []): CursorPaginator
    {
        return CleaningTask::query()
            ->select([
                'id',
                'cleaning_number',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'cleaning_type',
                'cleaning_scope',
                'status',
                'priority',
                'scheduled_date',
                'scheduled_start_at',
                'inspection_required',
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

    private function authorizeProperty(User $host, int $propertyId): void
    {
        $ownsProperty = Property::query()
            ->whereKey($propertyId)
            ->where('host_user_id', $host->id)
            ->exists();

        if (! $ownsProperty) {
            throw new AuthorizationException;
        }
    }

    private function scopeForContext(?int $sleepingPlaceId, ?int $roomId): string
    {
        if ($sleepingPlaceId) {
            return 'sleeping_place';
        }

        return $roomId ? 'room' : 'property';
    }

    private function dateString(CarbonInterface|string|null $date): ?string
    {
        return $date instanceof CarbonInterface ? $date->toDateString() : ($date === null ? null : (string) $date);
    }

    private function dateTime(CarbonInterface|string|null $date, mixed $time): ?CarbonImmutable
    {
        if (! $date) {
            return null;
        }

        $dateString = $this->dateString($date);
        $timeString = $time instanceof CarbonInterface ? $time->format('H:i') : ($time ?: '12:00');

        return CarbonImmutable::parse($dateString.' '.$timeString);
    }
}
