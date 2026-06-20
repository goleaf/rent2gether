<?php

namespace App\Livewire\Host\SleepingPlaces;

use App\Livewire\Host\SleepingPlaces\Concerns\HandlesSleepingPlaceStep;
use App\Models\SleepingPlace;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlaceMediaStep extends Component
{
    use HandlesSleepingPlaceStep;

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlace($sleepingPlace);
    }

    public function render(): View
    {
        return view('livewire.host.sleeping-places.sleeping-place-media-step');
    }
}
