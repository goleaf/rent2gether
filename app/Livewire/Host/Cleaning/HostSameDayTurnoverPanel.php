<?php

namespace App\Livewire\Host\Cleaning;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostSameDayTurnoverPanel extends Component
{
    public ?int $previousBookingId = null;

    public ?int $nextBookingId = null;

    public function render(): View
    {
        return view('livewire.host.cleaning.host-same-day-turnover-panel');
    }
}
