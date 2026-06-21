<?php

namespace App\Livewire\Host\CheckIn;

use App\Livewire\Bookings\CheckIn\Concerns\LoadsBookingCheckIn;
use Illuminate\View\View;
use Livewire\Component;

class HostCheckInsPage extends Component
{
    use LoadsBookingCheckIn;

    public function render(): View
    {
        return view('livewire.host.check-in.details-sheet', $this->checkInViewData('host_check_ins_page'));
    }
}
