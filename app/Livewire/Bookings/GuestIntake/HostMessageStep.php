<?php

namespace App\Livewire\Bookings\GuestIntake;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostMessageStep extends Component
{
    public function render(): View
    {
        return view('livewire.bookings.guest-intake.host-message-step');
    }
}
