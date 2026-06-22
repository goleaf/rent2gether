<?php

namespace App\Livewire\Host\Inspections;

use App\Models\InspectionTask;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInspectionTaskCard extends Component
{
    public ?int $inspectionTaskId = null;

    public function render(): View
    {
        return view('livewire.host.inspections.host-inspection-task-card', [
            'inspection' => $this->inspection(),
        ]);
    }

    private function inspection(): ?InspectionTask
    {
        return $this->inspectionTaskId
            ? InspectionTask::query()->with(['room:id,title', 'sleepingPlace:id,display_name,place_number'])->find($this->inspectionTaskId)
            : null;
    }
}
