<?php

namespace App\Livewire\Host\Cleaning;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostCleaningScheduleForm extends Component
{
    public ?int $cleaningTaskId = null;

    public function render(): View
    {
        return view('livewire.host.cleaning.host-cleaning-schedule-form');
    }
}
