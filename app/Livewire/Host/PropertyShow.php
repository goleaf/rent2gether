<?php

namespace App\Livewire\Host;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PropertyShow extends Component
{
    #[Locked]
    public Property $property;

    public function mount(Property $property): void
    {
        $this->property = $property->load('rooms.beds');
    }

    public function deleteRoom(int $roomId): void
    {
        $this->property->rooms()->where('id', $roomId)->delete();
        $this->property->refresh();
        $this->property->load('rooms.beds');
    }

    public function deleteBed(int $bedId): void
    {
        \App\Models\Bed::where('id', $bedId)
            ->whereHas('room', fn ($q) => $q->where('property_id', $this->property->id))
            ->delete();

        $this->property->refresh();
        $this->property->load('rooms.beds');
    }

    public function render(): View
    {
        return view('livewire.host.property-show');
    }
}
