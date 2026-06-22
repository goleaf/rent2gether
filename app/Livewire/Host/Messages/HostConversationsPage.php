<?php

namespace App\Livewire\Host\Messages;

use App\Models\Conversation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HostConversationsPage extends Component
{
    public string $status = 'active';

    #[Computed]
    public function conversations(): mixed
    {
        abort_unless(auth()->check(), 403);

        return Conversation::query()
            ->select(['id', 'conversation_type', 'status', 'guest_user_id', 'host_user_id', 'last_message_at', 'has_urgent_messages'])
            ->where('host_user_id', auth()->id())
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->with(['guest:id,name', 'lastConversationMessage:id,conversation_id,body,translation_key,translation_params_json'])
            ->orderByDesc('has_urgent_messages')
            ->orderByDesc('last_message_at')
            ->cursorPaginate(20);
    }

    public function render(): View
    {
        return view('livewire.host.messages.host-conversations-page', [
            'conversations' => $this->conversations,
        ])->layout('layouts.app', [
            'title' => __('messages.title'),
        ]);
    }
}
