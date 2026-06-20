<?php

namespace App\Livewire\Listings\Detail;

use App\Livewire\Listings\Detail\Concerns\LoadsSleepingPlaceProfileSection;
use App\Models\SleepingPlace;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlaceComfortSection extends Component
{
    use LoadsSleepingPlaceProfileSection;

    public function mount(int|SleepingPlace $sleepingPlace): void
    {
        $this->mountSleepingPlaceSection($sleepingPlace);
    }

    public function render(): View
    {
        return view('livewire.listings.detail.sleeping-place-comfort-section', [
            'section' => $this->section('comfort'),
        ]);
    }
}
