<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationBell extends Component
{
    #[Computed]
    public function unreadCount(): int
    {
        return Notification::query()
            ->forUser(auth()->id())
            ->unread()
            ->count();
    }

    public function render(): View
    {
        return view('livewire.notifications.notification-bell');
    }
}
