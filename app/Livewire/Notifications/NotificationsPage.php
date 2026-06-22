<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationsPage extends Component
{
    private const INITIAL_NOTIFICATION_LIMIT = 20;

    public function markAsRead(string $notificationId): void
    {
        Notification::query()
            ->forUser(auth()->id())
            ->findOrFail($notificationId)
            ->markRead();

        unset($this->items, $this->unreadCount);
    }

    public function markAllAsRead(): void
    {
        Notification::query()
            ->forUser(auth()->id())
            ->unread()
            ->update([
                'read_at' => now(),
                'status' => 'read',
                'is_read' => true,
            ]);

        unset($this->items, $this->unreadCount);
    }

    #[Computed]
    public function unreadCount(): int
    {
        return Notification::query()
            ->forUser(auth()->id())
            ->unread()
            ->count();
    }

    #[Computed]
    public function items()
    {
        return Notification::query()
            ->select([
                'id',
                'type',
                'user_id',
                'data',
                'title_key',
                'body_key',
                'action_url',
                'status',
                'read_at',
                'created_at',
            ])
            ->forUser(auth()->id())
            ->recent()
            ->limit(self::INITIAL_NOTIFICATION_LIMIT)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.notifications.notifications-page')
            ->layout('layouts.app', ['title' => __('notifications.page.title')]);
    }
}
