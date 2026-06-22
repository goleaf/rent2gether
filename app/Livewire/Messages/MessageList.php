<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use App\Services\Messaging\ConversationPrivacyService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MessageList extends Component
{
    #[Locked]
    public int $conversationId;

    public function mount(Conversation|int $conversation): void
    {
        $this->conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;
    }

    /**
     * @return list<array<string, mixed>>
     */
    #[Computed]
    public function messages(): array
    {
        $conversation = Conversation::query()->findOrFail($this->conversationId);

        abort_unless(auth()->check() && app(ConversationPrivacyService::class)->canViewConversation(auth()->user(), $conversation), 403);

        return $conversation
            ->conversationMessages()
            ->select(['id', 'conversation_id', 'sender_user_id', 'body', 'translation_key', 'translation_params_json', 'is_system', 'is_important', 'is_urgent', 'is_internal_note', 'sent_at'])
            ->latest('sent_at')
            ->limit(30)
            ->get()
            ->reverse()
            ->filter(fn ($message): bool => app(ConversationPrivacyService::class)->canViewMessage(auth()->user(), $message))
            ->map(fn ($message): array => [
                'id' => $message->id,
                'mine' => (int) $message->sender_user_id === (int) auth()->id(),
                'body' => $message->is_system && $message->translation_key ? __($message->translation_key, $message->translation_params_json ?? []) : $message->body,
                'is_system' => $message->is_system,
                'is_important' => $message->is_important,
                'is_urgent' => $message->is_urgent,
            ])
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.messages.message-list', [
            'messages' => $this->messages,
        ]);
    }
}
