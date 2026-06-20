<?php

namespace App\Livewire\Host\Rooms;

use App\Models\Room;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RoomEditPage extends Component
{
    #[Locked]
    public int $roomId;

    public function mount(Room $room): void
    {
        $this->roomId = $room->id;
    }

    #[Computed]
    public function room(): ?Room
    {
        return Room::query()
            ->select(['id', 'property_id', 'title', 'type', 'room_type', 'status', 'publication_status', 'sleeping_places_count'])
            ->withCount('sleepingPlaces')
            ->find($this->roomId);
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-edit-page');
    }
}
