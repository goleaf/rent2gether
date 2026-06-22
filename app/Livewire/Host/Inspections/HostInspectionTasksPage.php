<?php

namespace App\Livewire\Host\Inspections;

use App\Services\Cleaning\InspectionTaskService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostInspectionTasksPage extends Component
{
    public ?string $status = null;

    public function render(): View
    {
        $host = auth()->user();

        return view('livewire.host.inspections.host-inspection-tasks-page', [
            'tasks' => $host ? app(InspectionTaskService::class)->getForHost($host, [
                'status' => $this->status,
            ]) : null,
        ]);
    }
}
