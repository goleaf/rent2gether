<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationQuietHoursSettings extends Component
{
    public bool $enabled = false;

    public ?string $start = null;

    public ?string $end = null;

    public function render(): View
    {
        return view('livewire.notifications.notification-quiet-hours-settings');
    }
}
