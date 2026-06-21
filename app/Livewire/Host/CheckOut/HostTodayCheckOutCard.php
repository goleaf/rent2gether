<?php

namespace App\Livewire\Host\CheckOut;

use App\Models\BookingCheckOut;
use Illuminate\View\View;
use Livewire\Component;

class HostTodayCheckOutCard extends Component
{
    public ?BookingCheckOut $checkOut = null;

    public function render(): View
    {
        return view('livewire.host.check-out.details-sheet', ['checkOut' => $this->checkOut, 'variant' => 'today_card']);
    }
}
