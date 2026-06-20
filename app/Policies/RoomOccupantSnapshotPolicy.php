<?php

namespace App\Policies;

use App\Models\RoomOccupantSnapshot;
use App\Models\User;

class RoomOccupantSnapshotPolicy
{
    public function view(User $user, RoomOccupantSnapshot $snapshot): bool
    {
        return (int) $snapshot->user_id === (int) $user->id
            || $this->viewHost($user, $snapshot);
    }

    public function viewHost(User $user, RoomOccupantSnapshot $snapshot): bool
    {
        return (int) $snapshot->room?->property?->host_user_id === (int) $user->id;
    }
}
