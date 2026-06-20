<?php

namespace App\Livewire\Host\Rooms;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RoomCreatePage extends Component
{
    #[Locked]
    public int $propertyId;

    public function mount(Property $property): void
    {
        $this->propertyId = $property->id;
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-create-page');
    }
}
