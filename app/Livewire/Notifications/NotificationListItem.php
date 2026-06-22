<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationListItem extends Component
{
    public string $notificationId;

    public function render(): View
    {
        return view('livewire.notifications.notification-list-item', [
            'notification' => Notification::query()->find($this->notificationId),
        ]);
    }
}
