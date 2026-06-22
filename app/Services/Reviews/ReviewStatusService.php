<?php

namespace App\Services\Reviews;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\ReviewStatusLog;
use App\Models\User;

class ReviewStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(Review $review, string|ReviewStatus $newStatus, ?User $user = null, array $context = []): Review
    {
        $statusValue = $newStatus instanceof ReviewStatus ? $newStatus->value : $newStatus;
        $oldStatus = $review->status instanceof ReviewStatus ? $review->status->value : (string) $review->status;

        $review->forceFill([
            'status' => $statusValue,
        ])->save();

        ReviewStatusLog::query()->create([
            'review_id' => $review->id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $statusValue,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $review->refresh();
    }

    public function canTransition(Review $review, string $newStatus): bool
    {
        $current = $review->status instanceof ReviewStatus ? $review->status->value : (string) $review->status;

        if ($current === 'published' && $newStatus === 'submitted') {
            return false;
        }

        return $current !== $newStatus;
    }
}
