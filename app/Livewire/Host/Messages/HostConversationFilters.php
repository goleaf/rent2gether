<?php

namespace App\Livewire\Host\Messages;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostConversationFilters extends Component
{
    public string $status = 'active';

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function render(): View
    {
        return view('livewire.host.messages.host-conversation-filters');
    }
}
