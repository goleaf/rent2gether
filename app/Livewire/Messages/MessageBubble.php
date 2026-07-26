<?php

namespace App\Livewire\Messages;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class MessageBubble extends Component
{
    /** @var array<string, mixed> */
    private array $message = [];

    /**
     * @param  array<string, mixed>  $message
     */
    public function mount(array $message): void
    {
        $this->message = $message;
    }

    public function render(): View
    {
        return view('livewire.messages.message-bubble', [
            'message' => $this->message,
        ]);
    }
}
