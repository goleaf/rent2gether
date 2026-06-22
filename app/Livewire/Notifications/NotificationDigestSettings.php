<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationDigestSettings extends Component
{
    public string $digestType = 'none';

    public function render(): View
    {
        return view('livewire.notifications.notification-digest-settings');
    }
}
