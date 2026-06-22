<?php

namespace App\Services\Reviews;

use App\Models\ComplaintCase;
use App\Models\DisputeCase;
use App\Models\RatingEvent;

class RatingImpactService
{
    public function canImpactRating(string $sourceType, int $sourceId): bool
    {
        if ($sourceType === 'complaint_case') {
            return ComplaintCase::query()
                ->whereKey($sourceId)
                ->whereIn('status', ['confirmed', 'resolved', 'closed'])
                ->exists();
        }

        return true;
    }

    public function shouldFreezeImpact(string $sourceType, int $sourceId): bool
    {
        if ($sourceType === 'dispute_case') {
            return DisputeCase::query()
                ->whereKey($sourceId)
                ->where(function ($query): void {
                    $query->where('rating_impact_frozen', true)
                        ->orWhereIn('status', ['opened', 'in_review', 'waiting_response']);
                })
                ->exists();
        }

        return false;
    }

    public function applyImpact(RatingEvent $event): void
    {
        if ($event->frozen || $event->ignored || ! $event->confirmed) {
            return;
        }

        $event->forceFill(['ignored' => false])->save();
    }

    public function removeImpact(RatingEvent $event): void
    {
        $event->forceFill([
            'ignored' => true,
            'reason_key' => 'rating_impact_removed',
        ])->save();
    }
}
