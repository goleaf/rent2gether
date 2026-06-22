<?php

namespace App\Livewire\Host\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostNotificationListItem extends Component
{
    public string $notificationId;

    public function render(): View
    {
        return view('livewire.host.notifications.host-notification-list-item');
    }
}
