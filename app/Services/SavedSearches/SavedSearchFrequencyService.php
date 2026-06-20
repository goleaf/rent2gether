<?php

namespace App\Services\SavedSearches;

use App\Models\SavedSearch;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class SavedSearchFrequencyService
{
    public function shouldCheck(SavedSearch $search): bool
    {
        if ($search->status !== 'active' || ! $search->is_active) {
            return false;
        }

        $frequency = $search->notification_frequency ?: $search->notify_frequency ?: 'on_visit';

        if ($frequency === 'on_visit' || $frequency === 'instant') {
            return true;
        }

        $lastChecked = $this->dateTime($search->last_checked_at);

        if (! $lastChecked) {
            return true;
        }

        return match ($frequency) {
            'weekly' => $lastChecked->lessThanOrEqualTo(now()->subWeek()),
            'important_only' => $search->new_matches_count > 0
                || $search->price_drops_count > 0
                || $search->available_again_count > 0
                || $lastChecked->lessThanOrEqualTo(now()->subDay()),
            default => $lastChecked->lessThanOrEqualTo(now()->subDay()),
        };
    }

    public function shouldNotify(SavedSearch $search): bool
    {
        if ($this->isQuietHours($search)) {
            return false;
        }

        $frequency = $search->notification_frequency ?: 'on_visit';
        $lastNotified = $this->dateTime($search->last_notified_at);

        if (! $lastNotified || in_array($frequency, ['instant', 'on_visit'], true)) {
            return true;
        }

        return match ($frequency) {
            'weekly' => $lastNotified->lessThanOrEqualTo(now()->subWeek()),
            'important_only' => true,
            default => $lastNotified->lessThanOrEqualTo(now()->subDay()),
        };
    }

    public function calculateNextCheckAt(SavedSearch $search): ?Carbon
    {
        $now = now();

        return match ($search->notification_frequency ?: 'on_visit') {
            'instant' => $now->copy()->addMinutes(15),
            'weekly' => $now->copy()->addWeek(),
            default => $now->copy()->addDay(),
        };
    }

    public function isQuietHours(SavedSearch $search): bool
    {
        if (! $search->quiet_hours_enabled || ! $search->quiet_hours_start || ! $search->quiet_hours_end) {
            return false;
        }

        $now = now()->format('H:i');
        $start = $search->quiet_hours_start;
        $end = $search->quiet_hours_end;

        if ($start === $end) {
            return false;
        }

        if ($start < $end) {
            return $now >= $start && $now < $end;
        }

        return $now >= $start || $now < $end;
    }

    private function dateTime(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (! $value) {
            return null;
        }

        return Carbon::parse($value);
    }
}
