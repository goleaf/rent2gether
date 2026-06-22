<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class NotificationQuietHoursService
{
    public function __construct(private readonly NotificationPreferenceService $preferences) {}

    public function isQuietHours(User $user, ?CarbonInterface $time = null): bool
    {
        $preference = $this->preferences->getOrCreateForUser($user);

        if (! $preference->quiet_hours_enabled || ! $preference->quiet_hours_start || ! $preference->quiet_hours_end) {
            return false;
        }

        $current = Carbon::parse($time ?: now())->timezone($preference->timezone ?: config('app.timezone'));
        $start = Carbon::parse($current->toDateString().' '.$preference->quiet_hours_start, $current->timezone);
        $end = Carbon::parse($current->toDateString().' '.$preference->quiet_hours_end, $current->timezone);

        if ($start->lessThan($end)) {
            return $current->betweenIncluded($start, $end);
        }

        return $current->greaterThanOrEqualTo($start) || $current->lessThanOrEqualTo($end);
    }

    public function shouldDelayForQuietHours(User $user, Notification $notification): bool
    {
        if (! $this->isQuietHours($user)) {
            return false;
        }

        if ($notification->is_critical || $notification->priority === 'critical') {
            return ! $this->preferences->getOrCreateForUser($user)->critical_ignore_quiet_hours;
        }

        if ($notification->is_urgent || $notification->priority === 'urgent') {
            return false;
        }

        return in_array($notification->priority, ['low', 'normal'], true);
    }

    public function nextAllowedTime(User $user): Carbon
    {
        $preference = $this->preferences->getOrCreateForUser($user);
        $now = now()->timezone($preference->timezone ?: config('app.timezone'));

        if (! $preference->quiet_hours_end) {
            return $now;
        }

        $end = Carbon::parse($now->toDateString().' '.$preference->quiet_hours_end, $now->timezone);

        return $end->greaterThan($now) ? $end : $end->addDay();
    }
}
