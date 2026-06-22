<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use App\Services\Messaging\ConversationPrivacyService;
use App\Services\Messaging\MessageTemplateService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class QuickTemplatePicker extends Component
{
    #[Locked]
    public int $conversationId;

    public function mount(Conversation $conversation): void
    {
        abort_unless(auth()->check() && app(ConversationPrivacyService::class)->canViewConversation(auth()->user(), $conversation), 403);

        $this->conversationId = $conversation->id;
    }

    #[Computed]
    public function conversation(): Conversation
    {
        return Conversation::query()->findOrFail($this->conversationId);
    }

    /**
     * @return list<array{key:string,label:string}>
     */
    #[Computed]
    public function templates(): array
    {
        return app(MessageTemplateService::class)
            ->getAvailableForUser(auth()->user(), $this->conversation)
            ->map(fn ($template): array => [
                'key' => $template->template_key,
                'label' => __($template->title_translation_key),
            ])
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.messages.quick-template-picker', [
            'templates' => $this->templates,
        ]);
    }
}
