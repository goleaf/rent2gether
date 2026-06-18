<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ConversationList extends Component
{
    #[Computed]
    public function conversations()
    {
        $userId = auth()->id();

        return Conversation::where('participant_one_id', $userId)
            ->orWhere('participant_two_id', $userId)
            ->with(['participantOne', 'participantTwo', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.messages.conversation-list');
    }
}
