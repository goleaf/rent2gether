<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationFilters extends Component
{
    public string $active = 'all';

    public function render(): View
    {
        return view('livewire.notifications.notification-filters');
    }
}
