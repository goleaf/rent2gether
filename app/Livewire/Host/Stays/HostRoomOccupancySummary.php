<?php

namespace App\Livewire\Host\Stays;

use App\Models\Room;
use App\Services\Stays\RoomOccupancySnapshotService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostRoomOccupancySummary extends Component
{
    public ?int $roomId = null;

    public function mount(Room|int|null $room = null): void
    {
        $this->roomId = $room instanceof Room ? $room->id : ($room ? (int) $room : null);
    }

    public function render(): View
    {
        $room = $this->roomId ? Room::query()->find($this->roomId) : null;

        return view('livewire.host.stays.occupancy-summary', [
            'title' => __('stays.components.room_occupancy'),
            'summary' => $room ? app(RoomOccupancySnapshotService::class)->getForHost($room) : [],
        ]);
    }
}
