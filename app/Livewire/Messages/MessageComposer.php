<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use App\Services\Messaging\ConversationMessageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MessageComposer extends Component
{
    #[Locked]
    public int $conversationId;

    public string $body = '';

    public bool $important = false;

    public function mount(Conversation|int $conversation): void
    {
        $this->conversationId = $conversation instanceof Conversation ? $conversation->id : $conversation;
    }

    public function send(): void
    {
        $validated = $this->validate([
            'body' => ['required', 'string', 'max:5000'],
            'important' => ['boolean'],
        ], attributes: app('translator')->get('messages.validation_attributes'));

        try {
            app(ConversationMessageService::class)->sendText(auth()->user(), Conversation::query()->findOrFail($this->conversationId), $validated['body'], [
                'is_important' => $this->important,
            ]);
        } catch (AuthorizationException) {
            abort(403);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }
        }

        $this->body = '';
        $this->important = false;
    }

    public function render(): View
    {
        return view('livewire.messages.message-composer');
    }
}
