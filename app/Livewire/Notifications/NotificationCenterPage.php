<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use App\Services\Notifications\NotificationDueProcessorService;
use App\Services\Notifications\NotificationNotificationCenterService;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationCenterPage extends Component
{
    public string $filter = 'all';

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);

        app(NotificationDueProcessorService::class)->processDueForUser(auth()->user());
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;

        unset($this->notifications, $this->unreadCount, $this->urgentUnreadCount);
    }

    public function markRead(string $notificationId): void
    {
        $notification = Notification::query()->forUser(auth()->user())->findOrFail($notificationId);
        app(NotificationService::class)->markRead(auth()->user(), $notification);

        unset($this->notifications, $this->unreadCount, $this->urgentUnreadCount);
    }

    public function dismiss(string $notificationId): void
    {
        $notification = Notification::query()->forUser(auth()->user())->findOrFail($notificationId);
        app(NotificationService::class)->markDismissed(auth()->user(), $notification);

        unset($this->notifications, $this->unreadCount, $this->urgentUnreadCount);
    }

    public function markAllRead(): void
    {
        app(NotificationNotificationCenterService::class)->markAllRead(auth()->user());

        unset($this->notifications, $this->unreadCount, $this->urgentUnreadCount);
    }

    #[Computed]
    public function notifications(): mixed
    {
        return app(NotificationNotificationCenterService::class)->getForUser(auth()->user(), [
            'filter' => $this->filter,
        ]);
    }

    #[Computed]
    public function unreadCount(): int
    {
        return app(NotificationNotificationCenterService::class)->getUnreadCount(auth()->user());
    }

    #[Computed]
    public function urgentUnreadCount(): int
    {
        return app(NotificationNotificationCenterService::class)->getUrgentUnreadCount(auth()->user());
    }

    public function render(): View
    {
        return view('livewire.notifications.notification-center-page', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
            'urgentUnreadCount' => $this->urgentUnreadCount,
        ])->layout('layouts.app', [
            'title' => __('notifications.title'),
        ]);
    }
}
