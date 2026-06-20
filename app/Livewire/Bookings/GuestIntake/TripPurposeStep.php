<?php

namespace App\Livewire\Bookings\GuestIntake;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class TripPurposeStep extends Component
{
    public function render(): View
    {
        return view('livewire.bookings.guest-intake.trip-purpose-step');
    }
}
