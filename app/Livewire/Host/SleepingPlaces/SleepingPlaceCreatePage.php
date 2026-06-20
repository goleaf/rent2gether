<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Models\Room;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SleepingPlaceCreatePage extends Component
{
    #[Locked]
    public int $roomId;

    public function mount(Room $room): void
    {
        $this->roomId = $room->id;
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-create-page');
    }
}
