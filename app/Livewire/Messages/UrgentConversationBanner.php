<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class UrgentConversationBanner extends Component
{
    #[Locked]
    public int $conversationId;

    public function mount(Conversation|int $conversation): void
    {
        $this->conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;
    }

    public function render(): View
    {
        return view('livewire.messages.urgent-conversation-banner', [
            'conversation' => Conversation::query()->select(['id', 'has_urgent_messages'])->findOrFail($this->conversationId),
        ]);
    }
}
