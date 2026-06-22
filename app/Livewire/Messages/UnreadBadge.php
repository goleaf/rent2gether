<?php

namespace App\Livewire\Messages;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class UnreadBadge extends Component
{
    public int $count = 0;

    public function render(): View
    {
        return view('livewire.messages.unread-badge');
    }
}
