<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use App\Services\Messaging\ConversationMessageService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class InternalNoteComposer extends Component
{
    #[Locked]
    public int $conversationId;

    public string $note = '';

    public function mount(Conversation|int $conversation): void
    {
        $this->conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'note' => ['required', 'string', 'max:3000'],
        ], attributes: ['note' => __('messages.fields.internal_note')]);

        app(ConversationMessageService::class)->sendInternalNote(auth()->user(), Conversation::query()->findOrFail($this->conversationId), $validated['note']);
        $this->note = '';
    }

    public function render(): View
    {
        return view('livewire.messages.internal-note-composer');
    }
}
