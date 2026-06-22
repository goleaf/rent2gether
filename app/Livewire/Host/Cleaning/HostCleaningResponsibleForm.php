<?php

namespace App\Livewire\Host\Cleaning;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostCleaningResponsibleForm extends Component
{
    public ?int $cleaningTaskId = null;

    public string $responsibleType = 'not_assigned';

    public function render(): View
    {
        return view('livewire.host.cleaning.host-cleaning-responsible-form');
    }
}
