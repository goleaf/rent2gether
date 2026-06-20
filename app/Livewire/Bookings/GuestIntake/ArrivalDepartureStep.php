<?php

namespace App\Livewire\Bookings\GuestIntake;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ArrivalDepartureStep extends Component
{
    public function render(): View
    {
        return view('livewire.bookings.guest-intake.arrival-departure-step');
    }
}
