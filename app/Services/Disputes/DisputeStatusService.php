<?php

namespace App\Services\Disputes;

use App\Models\Booking;
use App\Models\DisputeCase;
use App\Models\DisputeStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class DisputeStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(DisputeCase $dispute, string $newStatus, ?User $user = null, array $context = []): DisputeCase
    {
        if (! $this->canTransition($dispute, $newStatus)) {
            return $dispute->fresh();
        }

        $oldStatus = $dispute->status;
        $attributes = ['status' => $newStatus];

        if ($newStatus === 'resolved') {
            $attributes['resolved_at'] = now();
        }

        if ($newStatus === 'closed') {
            $attributes['closed_at'] = now();
        }

        $dispute->forceFill($attributes)->save();

        DisputeStatusLog::query()->create([
            'dispute_case_id' => $dispute->id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $dispute->fresh();
    }

    public function canTransition(DisputeCase $dispute, string $newStatus): bool
    {
        return $dispute->status !== $newStatus;
    }

    public function syncBookingStatus(DisputeCase $dispute): ?Booking
    {
        $dispute->loadMissing('booking');

        if (! $dispute->booking instanceof Booking) {
            return null;
        }

        $attributes = [];

        if (Schema::hasColumn('bookings', 'has_dispute')) {
            $attributes['has_dispute'] = true;
        }

        if (Schema::hasColumn('bookings', 'status')) {
            $attributes['status'] = 'dispute_opened';
        }

        if ($attributes !== []) {
            $dispute->booking->forceFill($attributes)->save();
        }

        return $dispute->booking->fresh();
    }
}
