<?php

namespace App\Livewire\Host\Cleaning;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class HostCleaningMediaUploader extends Component
{
    use WithFileUploads;

    public ?int $cleaningTaskId = null;

    public function render(): View
    {
        return view('livewire.host.cleaning.host-cleaning-media-uploader');
    }
}
