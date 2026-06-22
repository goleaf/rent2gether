<?php

namespace App\Livewire\Messages;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class SystemEventMessage extends Component
{
    public string $translationKey = '';

    /** @var array<string, mixed> */
    public array $params = [];

    public function render(): View
    {
        return view('livewire.messages.system-event-message');
    }
}
