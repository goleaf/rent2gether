<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationCategorySettings extends Component
{
    public string $category = 'booking';

    public function render(): View
    {
        return view('livewire.notifications.notification-category-settings');
    }
}
