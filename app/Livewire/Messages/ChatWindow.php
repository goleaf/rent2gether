<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use App\Services\MessageService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ChatWindow extends Component
{
    #[Locked]
    public Conversation $conversation;

    public string $newMessage = '';

    public function mount(Conversation $conversation): void
    {
        $this->conversation = $conversation;
        app(MessageService::class)->markConversationRead($conversation, auth()->user());
    }

    #[Computed]
    public function messages()
    {
        return $this->conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();
    }

    #[Computed]
    public function otherUser()
    {
        return $this->conversation->participant_one_id === auth()->id()
            ? $this->conversation->participantTwo
            : $this->conversation->participantOne;
    }

    public function send(): void
    {
        $this->validate(['newMessage' => ['required', 'string', 'max:5000']]);

        app(MessageService::class)->send($this->conversation, auth()->user(), $this->newMessage);
        $this->newMessage = '';
        unset($this->messages);
    }

    public function render(): View
    {
        return view('livewire.messages.chat-window');
    }
}
