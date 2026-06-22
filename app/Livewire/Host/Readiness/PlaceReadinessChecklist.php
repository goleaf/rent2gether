<?php

namespace App\Livewire\Host\Readiness;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class PlaceReadinessChecklist extends Component
{
    public ?int $readinessCheckId = null;

    public function render(): View
    {
        return view('livewire.host.readiness.place-readiness-checklist');
    }
}
