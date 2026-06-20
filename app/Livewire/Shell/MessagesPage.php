<?php

namespace App\Livewire\Shell;

use App\Models\MessageThread;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
        return view('livewire.messages.inbox')
            ->layout('layouts.app', ['title' => __('shell.pages.guest.messages.title')]);
    }
}
