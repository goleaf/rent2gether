<?php

namespace App\Livewire\Host\Cleaning;

use App\Services\Cleaning\CleaningTaskService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostCleaningTasksPage extends Component
{
    public ?string $status = null;

    public function render(): View
    {
        $host = auth()->user();

        return view('livewire.host.cleaning.host-cleaning-tasks-page', [
            'tasks' => $host ? app(CleaningTaskService::class)->getForHost($host, [
                'status' => $this->status,
            ]) : null,
        ]);
    }
}
