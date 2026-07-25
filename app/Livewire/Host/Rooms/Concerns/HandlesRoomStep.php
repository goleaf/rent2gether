<?php

namespace App\Livewire\Host\Rooms\Concerns;

use App\Models\Room;
use App\Models\User;
use Livewire\Attributes\Locked;

trait HandlesRoomStep
{
    #[Locked]
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
        $room = Room::query()->with('property')->findOrFail($this->roomId);
        $user = auth()->user();

        abort_unless($user instanceof User && $room->property?->isOwnedBy($user), 403);

        return $room;
    }

    protected function markSaved(): void
    {
        $this->saved = true;
        $this->wasSaved = true;
    }
}
