<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ConversationContextCard extends Component
{
    #[Locked]
    public int $conversationId;

    public function mount(Conversation|int $conversation): void
    {
        $this->conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;
    }

    public function render(): View
    {
        return view('livewire.messages.conversation-context-card', [
            'conversation' => Conversation::query()
                ->select(['id', 'conversation_type', 'booking_id', 'sleeping_place_id'])
                ->with(['booking:id,booking_number,reference,check_in_date,check_out_date', 'sleepingPlace:id,display_name'])
                ->findOrFail($this->conversationId),
        ]);
    }
}
