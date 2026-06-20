<?php

namespace App\Livewire\Host\Rooms;

use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoomMediaStep extends Component
{
    use HandlesRoomStep;

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-media-step');
    }
}
