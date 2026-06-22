<?php

namespace App\Livewire\Host\Inspections;

use App\Models\InspectionTask;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInspectionDetailsSheet extends Component
{
    public ?int $inspectionTaskId = null;

    public function render(): View
    {
        return view('livewire.host.inspections.host-inspection-details-sheet', [
            'inspection' => $this->inspection(),
        ]);
    }

    private function inspection(): ?InspectionTask
    {
        if (! $this->inspectionTaskId) {
            return null;
        }

        return InspectionTask::query()
            ->select([
                'id',
                'inspection_number',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'inspection_type',
                'inspection_scope',
                'status',
                'priority',
                'scheduled_at',
                'result_summary',
            ])
            ->with([
                'room:id,title',
                'sleepingPlace:id,display_name,place_number',
                'items:id,inspection_task_id,item_key,label_key,status,sort_order',
            ])
            ->find($this->inspectionTaskId);
    }
}
