<?php

namespace App\Livewire\Messages;

use App\Services\Messaging\ConversationSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ConversationListPage extends Component
{
    #[Computed]
    public function conversations(): mixed
    {
        abort_unless(auth()->check(), 403);

        return app(ConversationSearchService::class)->getRecentForUser(auth()->user());
    }

    public function render(): View
    {
        return view('livewire.messages.conversation-list-page', [
            'conversations' => $this->conversations,
        ])->layout('layouts.app', [
            'title' => __('messages.title'),
        ]);
    }
}
