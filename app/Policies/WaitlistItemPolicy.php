<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WaitlistItem;

class WaitlistItemPolicy
{
    public function view(User $user, WaitlistItem $item): bool
    {
        return $item->user_id === $user->id || $this->hostCanView($user, $item);
    }

    public function update(User $user, WaitlistItem $item): bool
    {
        return $item->user_id === $user->id;
    }

    public function delete(User $user, WaitlistItem $item): bool
    {
        return $item->user_id === $user->id;
    }

    public function cancel(User $user, WaitlistItem $item): bool
    {
        return $item->user_id === $user->id;
    }

    public function viewHost(User $user, WaitlistItem $item): bool
    {
        return $this->hostCanView($user, $item);
    }

    private function hostCanView(User $user, WaitlistItem $item): bool
    {
        $property = $item->property ?: $item->sleepingPlace?->property;

        return $property?->host_user_id === $user->id;
    }
}
