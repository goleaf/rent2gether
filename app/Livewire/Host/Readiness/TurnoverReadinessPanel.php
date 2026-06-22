<?php

namespace App\Livewire\Host\Readiness;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class TurnoverReadinessPanel extends Component
{
    public ?int $previousBookingId = null;

    public ?int $nextBookingId = null;

    public function render(): View
    {
        return view('livewire.host.readiness.turnover-readiness-panel');
    }
}
