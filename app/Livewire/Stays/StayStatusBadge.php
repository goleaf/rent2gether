<?php

namespace App\Livewire\Stays;

use App\Livewire\Stays\Concerns\LoadsBookingStay;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class StayStatusBadge extends Component
{
    use LoadsBookingStay;

    public function render(): View
    {
        return view('livewire.stays.card', $this->stayViewData('status_badge'));
    }
}
