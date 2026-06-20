<?php

namespace App\Services\HostHints;

use App\Models\HostHintDismissal;
use App\Models\HostHintSnapshot;
use App\Models\User;
use Carbon\CarbonInterface;

class HostHintDismissalService
{
    public function dismiss(User $host, HostHintSnapshot $hint, ?CarbonInterface $until = null): HostHintDismissal
    {
        return HostHintDismissal::query()->updateOrCreate(
            [
                'user_id' => $host->id,
                'hint_key' => $hint->hint_key,
                'property_id' => $hint->property_id,
                'room_id' => $hint->room_id,
                'sleeping_place_id' => $hint->sleeping_place_id,
                'context' => null,
            ],
            [
                'dismissed_until' => $until,
                'dismissed_at' => now(),
            ],
        );
    }

    public function remindLater(User $host, HostHintSnapshot $hint, CarbonInterface $until): HostHintDismissal
    {
        return $this->dismiss($host, $hint, $until);
    }

    public function isDismissed(User $host, HostHintSnapshot $hint, string $context = 'dashboard'): bool
    {
        if ($context === 'before_publish' && $hint->isCriticalBeforePublish()) {
            return false;
        }

        return HostHintDismissal::query()
            ->where('user_id', $host->id)
            ->where('hint_key', $hint->hint_key)
            ->where(function ($query) use ($hint): void {
                $query->whereNull('property_id')->orWhere('property_id', $hint->property_id);
            })
            ->where(function ($query) use ($hint): void {
                $query->whereNull('room_id')->orWhere('room_id', $hint->room_id);
            })
            ->where(function ($query) use ($hint): void {
                $query->whereNull('sleeping_place_id')->orWhere('sleeping_place_id', $hint->sleeping_place_id);
            })
            ->where(function ($query): void {
                $query->whereNull('dismissed_until')->orWhere('dismissed_until', '>', now());
            })
            ->exists();
    }

    public function restoreExpiredDismissals(): int
    {
        return HostHintDismissal::query()
            ->whereNotNull('dismissed_until')
            ->where('dismissed_until', '<=', now())
            ->delete();
    }
}
