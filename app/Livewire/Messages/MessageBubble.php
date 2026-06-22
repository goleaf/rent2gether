<?php

namespace App\Livewire\Messages;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class MessageBubble extends Component
{
    /** @var array<string, mixed> */
    public array $message = [];

    public function render(): View
    {
        return view('livewire.messages.message-bubble');
    }
}
