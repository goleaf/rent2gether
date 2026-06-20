<?php

namespace App\Livewire\Host\Listings\Steps;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CalendarStep extends Component
{
    public int $propertyId;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;
    }

    public function render(): View
    {
        return view('livewire.host.listings.steps.calendar-step');
    }
}
