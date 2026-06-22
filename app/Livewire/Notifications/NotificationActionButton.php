<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationActionButton extends Component
{
    public string $notificationId;

    public function render(): View
    {
        return view('livewire.notifications.notification-action-button', [
            'notification' => Notification::query()->forUser(auth()->user())->find($this->notificationId),
        ]);
    }
}
