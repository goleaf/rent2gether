<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use App\Services\Messaging\ConversationPrivacyService;
use App\Services\Messaging\ConversationReadService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ConversationPage extends Component
{
    #[Locked]
    public int $conversationId;

    public string $body = '';

    public function mount(Conversation $conversation): void
    {
        abort_unless(auth()->check() && app(ConversationPrivacyService::class)->canViewConversation(auth()->user(), $conversation), 403);

        $this->conversationId = $conversation->id;
        app(ConversationReadService::class)->markConversationRead(auth()->user(), $conversation);
    }

    #[Computed]
    public function conversation(): Conversation
    {
        return Conversation::query()
            ->select([
                'id',
                'conversation_number',
                'conversation_type',
                'status',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'booking_id',
                'last_message_at',
                'guest_unread_count',
                'host_unread_count',
                'has_urgent_messages',
                'has_important_messages',
                'guest_can_write',
                'host_can_write',
                'is_read_only',
                'is_system_only',
            ])
            ->with([
                'guest:id,name',
                'host:id,name',
                'sleepingPlace:id,display_name',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
                'booking:id,booking_number,reference,check_in_date,check_out_date',
            ])
            ->findOrFail($this->conversationId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    #[Computed]
    public function messageCards(): array
    {
        return $this->conversation
            ->conversationMessages()
            ->select([
                'id',
                'conversation_id',
                'sender_user_id',
                'sender_type',
                'recipient_user_id',
                'message_type',
                'body',
                'translation_key',
                'translation_params_json',
                'is_system',
                'is_important',
                'is_urgent',
                'is_internal_note',
                'sent_at',
                'created_at',
            ])
            ->where(function ($query): void {
                $query->where('is_internal_note', false)
                    ->orWhere('sender_user_id', auth()->id());
            })
            ->latest('sent_at')
            ->limit(30)
            ->get()
            ->reverse()
            ->filter(fn ($message): bool => app(ConversationPrivacyService::class)->canViewMessage(auth()->user(), $message))
            ->map(fn ($message): array => [
                'id' => $message->id,
                'mine' => (int) $message->sender_user_id === (int) auth()->id(),
                'body' => $message->is_system && $message->translation_key
                    ? __($message->translation_key, $message->translation_params_json ?? [])
                    : $message->body,
                'is_system' => $message->is_system,
                'is_important' => $message->is_important,
                'is_urgent' => $message->is_urgent,
                'sent_at' => $message->sent_at,
            ])
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.messages.conversation-page', [
            'conversation' => $this->conversation,
            'messageCards' => $this->messageCards,
            'placeTitle' => $this->placeTitle(),
        ])->layout('layouts.app', [
            'title' => __('messages.title'),
        ]);
    }

    private function placeTitle(): ?string
    {
        $place = $this->conversation->sleepingPlace;

        return $place?->translations?->firstWhere('locale', app()->getLocale())?->title
            ?: $place?->translations?->firstWhere('locale', config('app.fallback_locale', 'en'))?->title
            ?: $place?->display_name;
    }
}
