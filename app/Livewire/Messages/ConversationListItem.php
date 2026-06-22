<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ConversationListItem extends Component
{
    #[Locked]
    public int $conversationId;

    public function mount(Conversation|int $conversation): void
    {
        $this->conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;
    }

    public function render(): View
    {
        return view('livewire.messages.conversation-list-item', [
            'conversation' => Conversation::query()
                ->select(['id', 'conversation_type', 'guest_user_id', 'host_user_id', 'last_message_at', 'has_urgent_messages'])
                ->with(['guest:id,name', 'host:id,name', 'lastConversationMessage:id,conversation_id,body,translation_key,translation_params_json'])
                ->findOrFail($this->conversationId),
        ]);
    }
}
