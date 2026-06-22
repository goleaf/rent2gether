<?php

namespace App\Livewire\Host\Inspections;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInspectionResultPanel extends Component
{
    public ?int $inspectionTaskId = null;

    public function render(): View
    {
        return view('livewire.host.inspections.host-inspection-result-panel');
    }
}
