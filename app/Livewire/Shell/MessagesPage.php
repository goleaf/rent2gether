<?php

namespace App\Livewire\Shell;

use App\Models\MessageThread;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MessagesPage extends Component
{
    #[Computed]
    public function threads()
    {
        $userId = (int) auth()->id();

        return MessageThread::query()
            ->select([
                'id',
                'type',
                'guest_user_id',
                'host_user_id',
                'booking_id',
                'sleeping_place_id',
                'last_message_at',
                'status',
            ])
            ->forParticipant($userId)
            ->with([
                'guest:id,name',
                'host:id,name',
                'sleepingPlace:id,display_name',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
                'messages' => fn ($query) => $query
                    ->select(['id', 'thread_id', 'sender_id', 'sender_user_id', 'recipient_user_id', 'body', 'read_at', 'created_at'])
                    ->latest()
                    ->limit(1),
            ])
            ->withCount([
                'messages as unread_count' => function (Builder $query) use ($userId): void {
                    $query
                        ->whereNull('read_at')
                        ->where(function (Builder $query) use ($userId): void {
                            $query->where('recipient_user_id', $userId)
                                ->orWhere(function (Builder $query) use ($userId): void {
                                    $query->whereNull('recipient_user_id')
                                        ->where('sender_id', '!=', $userId);
                                });
                        });
                },
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.messages.inbox', [
            'page' => $this->page(),
            'threadCards' => $this->threadCards(),
        ])
            ->layout('layouts.app', ['title' => __('shell.pages.guest.messages.title')]);
    }

    /**
     * @return array<string, string>
     */
    private function page(): array
    {
        return [
            'eyebrow' => __('shell.pages.guest.messages.eyebrow'),
            'title' => __('shell.pages.guest.messages.title'),
            'helper' => __('shell.pages.guest.messages.helper'),
            'action' => __('shell.pages.guest.messages.action'),
            'empty_title' => __('shell.pages.guest.messages.empty_title'),
            'empty_text' => __('shell.pages.guest.messages.empty_text'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function threadCards(): array
    {
        return $this->threads
            ->map(function (MessageThread $thread): array {
                $other = (int) $thread->guest_user_id === (int) auth()->id() ? $thread->host : $thread->guest;
                $lastMessage = $thread->messages->first();

                return [
                    'thread' => $thread,
                    'other_name' => $other?->name ?: __('messages.inbox.unknown_user'),
                    'thread_type' => $thread->type?->value ?? 'pre_booking',
                    'place_title' => $this->placeTitle($thread),
                    'last_message' => $lastMessage ? Str::limit($lastMessage->body, 90) : __('messages.inbox.no_messages'),
                    'last_message_time' => $lastMessage?->created_at?->diffForHumans(),
                ];
            })
            ->values()
            ->all();
    }

    private function placeTitle(MessageThread $thread): ?string
    {
        return $thread->sleepingPlace?->translations?->firstWhere('locale', app()->getLocale())?->title
            ?: $thread->sleepingPlace?->translations?->firstWhere('locale', config('localization.fallback_locale', 'en'))?->title
            ?: $thread->sleepingPlace?->display_name;
    }
}
