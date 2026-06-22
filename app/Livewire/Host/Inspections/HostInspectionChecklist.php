<?php

namespace App\Livewire\Host\Inspections;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInspectionChecklist extends Component
{
    public ?int $inspectionTaskId = null;

    public function render(): View
    {
        return view('livewire.host.inspections.host-inspection-checklist');
    }
}
