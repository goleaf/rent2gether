<?php

namespace App\Services\Notifications;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class NotificationThrottleService
{
    public function shouldThrottle(User $user, string $throttleKey): bool
    {
        $until = $this->getThrottleUntil($user, $throttleKey);

        return $until instanceof Carbon && $until->isFuture();
    }

    public function setThrottle(User $user, string $throttleKey, CarbonInterface $until): void
    {
        Cache::put($this->cacheKey($user, $throttleKey), $until->toIso8601String(), $until);
    }

    public function getThrottleUntil(User $user, string $throttleKey): ?Carbon
    {
        $value = Cache::get($this->cacheKey($user, $throttleKey));

        return is_string($value) ? Carbon::parse($value) : null;
    }

    private function cacheKey(User $user, string $throttleKey): string
    {
        return 'notifications.throttle.'.$user->id.'.'.$throttleKey;
    }
}
