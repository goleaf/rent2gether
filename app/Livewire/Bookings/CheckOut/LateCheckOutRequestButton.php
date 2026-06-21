<?php

namespace App\Livewire\Bookings\CheckOut;

use App\Livewire\Bookings\CheckOut\Concerns\LoadsBookingCheckOut;
use Illuminate\View\View;
use Livewire\Component;

class LateCheckOutRequestButton extends Component
{
    use LoadsBookingCheckOut;

    public function render(): View
    {
        return view('livewire.bookings.check-out.card', $this->checkOutViewData('late_checkout'));
    }
}
