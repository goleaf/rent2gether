<?php

namespace App\Services\Hints;

use App\Models\GuestHintDismissal;
use App\Models\SleepingPlace;
use App\Models\User;

class HintDismissalService
{
    public function dismiss(User $user, string $hintKey, ?SleepingPlace $place, string $context): GuestHintDismissal
    {
        return GuestHintDismissal::query()->updateOrCreate([
            'user_id' => $user->id,
            'sleeping_place_id' => $place?->id,
            'hint_key' => $hintKey,
            'context' => $context,
        ], [
            'dismissed_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function isDismissed(?User $user, string $hintKey, ?SleepingPlace $place, string $context): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return GuestHintDismissal::query()
            ->active()
            ->where('user_id', $user->id)
            ->where('hint_key', $hintKey)
            ->where(function ($query) use ($place): void {
                $query->whereNull('sleeping_place_id');

                if ($place instanceof SleepingPlace) {
                    $query->orWhere('sleeping_place_id', $place->id);
                }
            })
            ->where(function ($query) use ($context): void {
                $query->whereNull('context')->orWhere('context', $context);
            })
            ->exists();
    }

    public function restoreExpiredDismissals(): int
    {
        return GuestHintDismissal::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();
    }
}
