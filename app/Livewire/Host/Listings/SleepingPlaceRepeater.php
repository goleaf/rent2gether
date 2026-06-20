<?php

namespace App\Livewire\Host\Listings;

use App\Models\Room;
use App\Services\HostListings\Wizard\HostSleepingPlaceDraftService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SleepingPlaceRepeater extends Component
{
    #[Locked]
    public int $roomId;

    public function mount(int $roomId): void
    {
        $this->roomId = $roomId;
    }

    public function autoCreate(HostSleepingPlaceDraftService $places): void
    {
        $room = Room::query()->findOrFail($this->roomId);
        $places->autoCreatePlacesForRoom($room, (int) $room->sleeping_places_count);
        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        return view('livewire.host.listings.sleeping-place-repeater', [
            'room' => Room::query()->with('sleepingPlaces')->find($this->roomId),
        ]);
    }
}
