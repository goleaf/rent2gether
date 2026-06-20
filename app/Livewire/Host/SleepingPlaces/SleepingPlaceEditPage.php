<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Models\SleepingPlace;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SleepingPlaceEditPage extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->sleepingPlaceId = $sleepingPlace->id;
    }

    #[Computed]
    public function sleepingPlace(): ?SleepingPlace
    {
        return SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id', 'title', 'display_name', 'place_type', 'type', 'status', 'publication_status', 'base_price', 'base_price_per_night', 'currency'])
            ->find($this->sleepingPlaceId);
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-edit-page');
    }
}
