<?php

namespace App\Livewire\Host\Rooms;

use App\Data\Occupants\DateRange;
use App\Models\Room;
use App\Services\Occupants\RoomOccupantSummaryService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoomOccupantsPreview extends Component
{
    public int $roomId;

    public string $checkIn;

    public string $checkOut;

    public function mount(Room $room, string $checkIn, string $checkOut): void
    {
        $room->load('property:id,host_user_id');

        abort_unless((int) $room->property?->host_user_id === (int) auth()->id(), 403);

        $this->roomId = $room->id;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
    }

    public function render(RoomOccupantSummaryService $summaries): View
    {
        $room = Room::query()
            ->select(['id', 'property_id', 'sleeping_places_count'])
            ->with('property:id,host_user_id')
            ->findOrFail($this->roomId);

        abort_unless((int) $room->property?->host_user_id === (int) auth()->id(), 403);

        return view('livewire.host.rooms.room-occupants-preview', [
            'summary' => $summaries->getPreBookingSummary($room, new DateRange($this->checkIn, $this->checkOut))->toArray(),
        ]);
    }
}
