<?php

namespace App\Livewire\Bookings\Dates;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class TimePreferenceSheet extends Component
{
    public string $checkInTime = '';

    public string $checkOutTime = '';

    public bool $earlyCheckInRequested = false;

    public bool $lateCheckOutRequested = false;

    public bool $flexibleCheckIn = false;

    public bool $flexibleCheckOut = false;

    public string $checkInComment = '';

    public string $checkOutComment = '';

    public function render(): View
    {
        return view('livewire.bookings.dates.time-preference-sheet');
    }
}
