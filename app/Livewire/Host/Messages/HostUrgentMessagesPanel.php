<?php

namespace App\Livewire\Host\Messages;

use App\Services\Messaging\ConversationSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HostUrgentMessagesPanel extends Component
{
    /**
     * @return list<array<string, mixed>>
     */
    #[Computed]
    public function conversations(): array
    {
        return app(ConversationSearchService::class)
            ->getUrgentForHost(auth()->user())
            ->map(fn ($conversation): array => [
                'id' => $conversation->id,
                'guest_name' => $conversation->guest?->name,
                'messages' => $conversation->conversationMessages
                    ->map(fn ($message): array => [
                        'body' => $message->body ?: ($message->translation_key ? __($message->translation_key, $message->translation_params_json ?? []) : null),
                        'sent_at' => $message->sent_at,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.host.messages.host-urgent-messages-panel', [
            'conversations' => $this->conversations,
        ]);
    }
}
