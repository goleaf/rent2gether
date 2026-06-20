<?php

namespace App\Livewire\Host\Rooms;

use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\Room;
use App\Services\Rooms\RoomCompletionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoomCompletionPanel extends Component
{
    use HandlesRoomStep;

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
    }

    /**
     * @return array{percentage:int,items:list<array{key:string,label:string,complete:bool}>}
     */
    public function completion(RoomCompletionService $completion): array
    {
        $room = $this->room()->loadMissing(['translations', 'layoutDetails', 'comfortDetails', 'accessDetails', 'conditionDetails']);

        return [
            'percentage' => $completion->percentage($room),
            'items' => $completion->items($room),
        ];
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-completion-panel', [
            'completion' => $this->completion(app(RoomCompletionService::class)),
        ]);
    }
}
