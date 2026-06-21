<?php

namespace App\Livewire\Host\Stays;

use App\Livewire\Stays\Concerns\LoadsBookingStay;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostCurrentResidentCard extends Component
{
    use LoadsBookingStay;

    public function render(): View
    {
        return view('livewire.host.stays.host-current-resident-card', $this->stayViewData('card'));
    }
}
