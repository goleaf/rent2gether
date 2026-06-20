<?php

namespace App\Livewire\Host\Rooms\Concerns;

use App\Models\Room;
use App\Models\User;

trait HandlesRoomStep
{
    public int $roomId;

    public bool $saved = false;

    public bool $wasSaved = false;

    protected function mountRoom(Room $room): void
    {
        $user = auth()->user();
        $room->loadMissing('property');

        abort_unless($user instanceof User && $room->property?->isOwnedBy($user), 403);

        $this->roomId = $room->id;
    }

    protected function room(): Room
    {
        return Room::query()->with('property')->findOrFail($this->roomId);
    }

    protected function markSaved(): void
    {
        $this->saved = true;
        $this->wasSaved = true;
    }
}
