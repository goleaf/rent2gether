<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationList extends Component
{
    public function render(): View
    {
        return view('livewire.notifications.notification-list');
    }
}
