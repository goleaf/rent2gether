<?php

namespace App\Livewire\Host\Listings;

use App\Models\Property;
use App\Services\HostListings\Wizard\HostRoomDraftService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RoomRepeater extends Component
{
    #[Locked]
    public int $propertyId;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
    }

    public function addRoom(HostRoomDraftService $rooms): void
    {
        $property = Property::query()->findOrFail($this->propertyId);
        $rooms->createRoom($property, []);
        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        return view('livewire.host.listings.room-repeater', [
            'rooms' => Property::query()->find($this->propertyId)?->rooms()->withCount('sleepingPlaces')->get() ?? collect(),
        ]);
    }
}
