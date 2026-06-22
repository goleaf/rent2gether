<?php

namespace App\Livewire\Notifications;

use App\Services\Notifications\NotificationNotificationCenterService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationUrgentBadge extends Component
{
    public function render(): View
    {
        return view('livewire.notifications.notification-urgent-badge', [
            'count' => auth()->check() ? app(NotificationNotificationCenterService::class)->getUrgentUnreadCount(auth()->user()) : 0,
        ]);
    }
}
