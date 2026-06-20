<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivitySummary;

class UserActivitySummaryService
{
    public function refresh(User $user): UserActivitySummary
    {
        $summary = $this->getOrCreate($user);
        $summary->forceFill([
            'last_activity_at' => now(),
        ])->save();

        return $summary->refresh();
    }

    public function incrementCompletedStayAsGuest(User $user): UserActivitySummary
    {
        return $this->increment($user, 'completed_stays_as_guest');
    }

    public function incrementCompletedStayAsHost(User $user): UserActivitySummary
    {
        return $this->increment($user, 'completed_stays_as_host');
    }

    public function recordGuestCancellation(User $user): UserActivitySummary
    {
        return $this->increment($user, 'cancelled_by_guest_count');
    }

    public function recordHostCancellation(User $user): UserActivitySummary
    {
        return $this->increment($user, 'cancelled_by_host_count');
    }

    public function recordNoShow(User $user): UserActivitySummary
    {
        return $this->increment($user, 'no_show_count');
    }

    public function syncRatings(User $user): UserActivitySummary
    {
        $summary = $this->getOrCreate($user);
        $summary->forceFill([
            'average_guest_rating' => $user->rating_as_guest,
            'average_host_rating' => $user->rating_as_host,
            'last_activity_at' => now(),
        ])->save();

        return $summary->refresh();
    }

    private function getOrCreate(User $user): UserActivitySummary
    {
        return UserActivitySummary::query()->firstOrCreate(['user_id' => $user->id]);
    }

    private function increment(User $user, string $column): UserActivitySummary
    {
        $summary = $this->getOrCreate($user);
        $summary->forceFill([
            $column => ((int) $summary->getAttribute($column)) + 1,
            'last_activity_at' => now(),
        ])->save();

        return $summary->refresh();
    }
}
