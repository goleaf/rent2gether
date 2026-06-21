<?php

namespace App\Livewire\Bookings\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use Illuminate\View\View;
use Livewire\Component;

class CheckInStepsList extends Component
{
    use LoadsBookingCheckIn;

    public function render(): View
    {
        return view('livewire.bookings.check-in.card', $this->checkInViewData('steps_list'));
    }
}
