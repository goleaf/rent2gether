<?php

namespace App\Livewire\Bookings\Availability;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AvailabilityWarnings extends Component
{
    /** @var list<string> */
    public array $reasons = [];

    public function render(): View
    {
        return view('livewire.bookings.availability.availability-warnings');
    }
}
