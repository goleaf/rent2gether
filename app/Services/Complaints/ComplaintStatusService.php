<?php

namespace App\Services\Complaints;

use App\Models\Booking;
use App\Models\BookingStay;
use App\Models\ComplaintCase;
use App\Models\ComplaintStatusLog;
use App\Models\User;

class ComplaintStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(ComplaintCase $case, string $newStatus, ?User $user = null, array $context = []): ComplaintCase
    {
        if (! $this->canTransition($case, $newStatus)) {
            return $case->fresh();
        }

        $oldStatus = $case->status;
        $attributes = ['status' => $newStatus];

        if ($newStatus === 'resolved') {
            $attributes['resolved_at'] = now();
        }

        if ($newStatus === 'closed') {
            $attributes['closed_at'] = now();
        }

        $case->forceFill($attributes)->save();

        ComplaintStatusLog::query()->create([
            'complaint_case_id' => $case->id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $case->fresh();
    }

    public function canTransition(ComplaintCase $case, string $newStatus): bool
    {
        return $case->status !== $newStatus;
    }

    public function syncBookingStatus(ComplaintCase $case): ?Booking
    {
        $case->loadMissing('booking');

        if (! $case->booking instanceof Booking) {
            return null;
        }

        $case->booking->forceFill(['has_complaint' => true])->save();

        return $case->booking->fresh();
    }

    public function syncStayStatus(ComplaintCase $case): ?BookingStay
    {
        $case->loadMissing('stay');

        if (! $case->stay instanceof BookingStay) {
            return null;
        }

        $case->stay->forceFill(['status' => 'problem_reported'])->save();

        return $case->stay->fresh();
    }
}
