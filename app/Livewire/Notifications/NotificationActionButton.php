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
        $notification = Notification::query()
            ->forUser(auth()->user())
            ->find($this->notificationId);

        $notification?->makeHidden('action_url');

        return view('livewire.notifications.notification-action-button', [
            'notification' => $notification,
        ]);
    }
}
