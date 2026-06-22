<?php

namespace App\Livewire\Messages;

use App\Services\Messaging\ConversationSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ConversationSearchBox extends Component
{
    public string $query = '';

    #[Computed]
    public function results(): mixed
    {
        if (trim($this->query) === '' || ! auth()->check()) {
            return collect();
        }

        return app(ConversationSearchService::class)->searchUserConversations(auth()->user(), $this->query);
    }

    public function render(): View
    {
        return view('livewire.messages.conversation-search-box', [
            'results' => $this->results,
        ]);
    }
}
