<?php

namespace App\Services\HostCleaning;

use App\Models\BookingDepositDecision;
use App\Models\BookingForgottenItem;
use App\Models\HostCleaningFinding;
use App\Models\HostCleaningTask;
use App\Models\HostInspectionTask;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;

class HostCleaningFindingService
{
    public function reportFinding(User $user, HostCleaningTask $task, array $data): HostCleaningFinding
    {
        $this->authorize($user, $task);

        $validated = Validator::make($data, [
            'finding_type' => ['required', 'string', 'max:80'],
            'severity' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string'],
            'photo_paths' => ['nullable', 'array'],
            'needs_host_action' => ['nullable', 'boolean'],
            'needs_guest_notification' => ['nullable', 'boolean'],
            'needs_repair' => ['nullable', 'boolean'],
            'needs_deposit_review' => ['nullable', 'boolean'],
        ])->validate();

        $finding = HostCleaningFinding::query()->create([
            'host_cleaning_task_id' => $task->id,
            'booking_id' => $task->booking_id,
            'finding_type' => $validated['finding_type'],
            'severity' => $validated['severity'] ?? 'medium',
            'description' => $validated['description'] ?? null,
            'photo_paths_json' => $validated['photo_paths'] ?? [],
            'needs_host_action' => (bool) ($validated['needs_host_action'] ?? false),
            'needs_guest_notification' => (bool) ($validated['needs_guest_notification'] ?? false),
            'needs_repair' => (bool) ($validated['needs_repair'] ?? false),
            'needs_deposit_review' => (bool) ($validated['needs_deposit_review'] ?? false),
            'status' => 'open',
        ]);

        $this->syncTaskFlags($task->fresh());

        return $finding;
    }

    public function markResolved(User $host, HostCleaningFinding $finding): HostCleaningFinding
    {
        $finding->loadMissing('task');
        $this->authorize($host, $finding->task);

        $finding->forceFill([
            'status' => 'resolved',
            'resolved_at' => now(),
        ])->save();

        $this->syncTaskFlags($finding->task->fresh());

        return $finding->refresh();
    }

    public function createRepairTaskIfNeeded(HostCleaningFinding $finding): ?HostInspectionTask
    {
        $finding->loadMissing('task');

        if (! $finding->needs_repair) {
            return null;
        }

        $task = $finding->task;
        $task->forceFill(['needs_repair' => true])->save();

        return HostInspectionTask::query()->firstOrCreate(
            [
                'booking_id' => $task->booking_id,
                'sleeping_place_id' => $task->sleeping_place_id,
                'reason' => 'cleaning_finding_repair',
            ],
            [
                'user_id' => $task->user_id,
                'property_id' => $task->property_id,
                'room_id' => $task->room_id,
                'booking_check_out_id' => $task->booking_check_out_id,
                'status' => 'needed',
                'scheduled_date' => $task->scheduled_date,
                'scheduled_time' => $task->scheduled_time,
                'note' => $finding->description,
            ],
        );
    }

    public function createDepositReviewIfNeeded(HostCleaningFinding $finding): ?BookingDepositDecision
    {
        $finding->loadMissing('task');

        if (! $finding->needs_deposit_review || ! $finding->task->booking_id || ! $finding->task->bookingCheckOut) {
            return null;
        }

        $checkOut = $finding->task->bookingCheckOut;

        return BookingDepositDecision::query()->firstOrCreate(
            ['booking_check_out_id' => $checkOut->id],
            [
                'booking_id' => $checkOut->booking_id,
                'guest_user_id' => $checkOut->guest_user_id,
                'host_user_id' => $checkOut->host_user_id,
                'deposit_amount' => null,
                'currency' => 'EUR',
                'decision' => 'return_partial',
                'deduction_amount' => null,
                'return_amount' => null,
                'reason' => $finding->description,
                'status' => 'pending_review',
            ],
        );
    }

    public function createForgottenItemRecordIfNeeded(HostCleaningFinding $finding): ?BookingForgottenItem
    {
        $finding->loadMissing('task.booking');
        $task = $finding->task;

        if ($finding->finding_type !== 'forgotten_items' || ! $task->booking_id || ! $task->booking) {
            return null;
        }

        return BookingForgottenItem::query()->firstOrCreate(
            [
                'booking_id' => $task->booking_id,
                'booking_check_out_id' => $task->booking_check_out_id,
                'description' => $finding->description,
            ],
            [
                'guest_user_id' => $task->booking->guest_user_id,
                'host_user_id' => $task->booking->host_user_id,
                'item_name' => null,
                'photo_paths_json' => $finding->photo_paths_json ?: [],
                'storage_location' => null,
                'status' => 'found',
                'keep_until' => now()->addDays(30)->toDateString(),
            ],
        );
    }

    private function syncTaskFlags(HostCleaningTask $task): void
    {
        $openFindings = $task->findings()->whereNotIn('status', ['resolved', 'closed'])->get();

        $task->forceFill([
            'has_damage_found' => $openFindings->whereIn('finding_type', ['damage', 'broken_item', 'missing_item'])->isNotEmpty(),
            'has_forgotten_items' => $openFindings->where('finding_type', 'forgotten_items')->isNotEmpty(),
            'has_extra_dirty' => $openFindings->whereIn('finding_type', ['extra_dirty', 'bad_smell', 'stains', 'mold', 'insects'])->isNotEmpty(),
            'needs_repair' => $openFindings->contains(fn (HostCleaningFinding $finding): bool => $finding->needs_repair),
        ])->save();
    }

    private function authorize(User $user, HostCleaningTask $task): void
    {
        if ((int) $task->user_id !== (int) $user->id && (int) $task->assigned_to_user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }
}
