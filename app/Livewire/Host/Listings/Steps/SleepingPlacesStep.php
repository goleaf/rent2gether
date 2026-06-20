<?php

namespace App\Livewire\Host\Listings\Steps;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SleepingPlacesStep extends Component
{
    public int $propertyId;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
    }

    public function render(): View
    {
        return view('livewire.host.listings.steps.sleeping-places-step', [
            'rooms' => Property::query()->with('rooms')->find($this->propertyId)?->rooms ?? collect(),
        ]);
    }
}
