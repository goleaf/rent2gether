<?php

namespace App\Livewire\Host\Inspections;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class HostInspectionMediaUploader extends Component
{
    use WithFileUploads;

    public ?int $inspectionTaskId = null;

    public function render(): View
    {
        return view('livewire.host.inspections.host-inspection-media-uploader');
    }
}
