<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ConversationHeader extends Component
{
    #[Locked]
    public int $conversationId;

    public function mount(Conversation|int $conversation): void
    {
        $this->conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;
    }

    public function render(): View
    {
        return view('livewire.messages.conversation-header', [
            'conversation' => Conversation::query()
                ->select(['id', 'conversation_type', 'status', 'sleeping_place_id', 'has_urgent_messages'])
                ->with(['sleepingPlace:id,display_name', 'sleepingPlace.translations:id,sleeping_place_id,locale,title'])
                ->findOrFail($this->conversationId),
        ]);
    }
}
