<?php

namespace App\Livewire\Notifications;

use App\Services\Notifications\NotificationNotificationCenterService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationUnreadBadge extends Component
{
    public function render(): View
    {
        return view('livewire.notifications.notification-unread-badge', [
            'count' => auth()->check() ? app(NotificationNotificationCenterService::class)->getUnreadCount(auth()->user()) : 0,
        ]);
    }
}
