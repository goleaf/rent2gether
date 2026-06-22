<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;
use App\Models\ComplaintResolutionOption;
use App\Models\User;

class ComplaintResolutionService
{
    public function __construct(
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
        private readonly ComplaintNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createResolutionOption(ComplaintCase $case, string $resolutionType, array $data): ComplaintResolutionOption
    {
        $option = ComplaintResolutionOption::query()->create([
            'complaint_case_id' => $case->id,
            'resolution_type' => $resolutionType,
            'status' => $data['status'] ?? 'offered',
            'description' => $data['description'] ?? $data['message'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? $case->currency,
            'booking_refund_id' => $data['booking_refund_id'] ?? null,
            'booking_relocation_id' => $data['booking_relocation_id'] ?? null,
            'booking_cancellation_id' => $data['booking_cancellation_id'] ?? null,
            'deposit_case_id' => $data['deposit_case_id'] ?? null,
            'maintenance_request_id' => $data['maintenance_request_id'] ?? null,
            'cleaning_task_id' => $data['cleaning_task_id'] ?? null,
            'offered_by_user_id' => $data['offered_by_user_id'] ?? null,
            'offered_at' => $data['offered_at'] ?? now(),
        ]);

        $case->forceFill([
            'resolution_type' => $resolutionType,
            'resolution_status' => 'offered',
        ])->save();

        $this->statuses->transition($case->fresh(), 'resolution_offered');
        $this->events->record($case->fresh(), 'resolution_offered', ['resolution_option_id' => $option->id]);
        $this->notifications->notifyResolutionOffered($case->fresh());

        return $option->fresh();
    }

    public function acceptResolution(User $user, ComplaintResolutionOption $option): ComplaintResolutionOption
    {
        $option->forceFill([
            'status' => 'accepted',
            'accepted_by_user_id' => $user->id,
            'accepted_at' => now(),
        ])->save();

        $case = $option->complaintCase;
        $case->forceFill(['resolution_status' => 'accepted'])->save();
        $this->statuses->transition($case->fresh(), 'resolution_accepted', $user);
        $this->events->record($case->fresh(), 'resolution_accepted', ['resolution_option_id' => $option->id, 'user_id' => $user->id]);
        $this->notifications->notifyResolutionAccepted($case->fresh());

        return $option->fresh();
    }

    public function rejectResolution(User $user, ComplaintResolutionOption $option): ComplaintResolutionOption
    {
        $option->forceFill([
            'status' => 'rejected',
            'rejected_at' => now(),
        ])->save();

        $case = $option->complaintCase;
        $case->forceFill(['resolution_status' => 'rejected'])->save();
        $this->statuses->transition($case->fresh(), 'resolution_rejected', $user);
        $this->events->record($case->fresh(), 'resolution_rejected', ['resolution_option_id' => $option->id, 'user_id' => $user->id]);
        $this->notifications->notifyResolutionRejected($case->fresh());

        return $option->fresh();
    }

    public function applyResolution(ComplaintResolutionOption $option): void
    {
        $option->forceFill(['status' => 'in_progress'])->save();
        $this->statuses->transition($option->complaintCase->fresh(), 'action_in_progress');
    }

    public function markCompleted(ComplaintResolutionOption $option): ComplaintResolutionOption
    {
        $option->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();

        $this->statuses->transition($option->complaintCase->fresh(), 'resolved');
        $this->events->record($option->complaintCase->fresh(), 'complaint_resolved', ['resolution_option_id' => $option->id]);

        return $option->fresh();
    }
}
