<?php

namespace App\Livewire\Messages;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class MessageAttachmentUploader extends Component
{
    use WithFileUploads;

    /** @var array<int, mixed> */
    public array $uploads = [];

    public function render(): View
    {
        return view('livewire.messages.message-attachment-uploader');
    }
}
