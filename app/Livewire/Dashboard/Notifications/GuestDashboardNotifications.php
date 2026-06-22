<?php

namespace App\Livewire\Dashboard\Notifications;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class GuestDashboardNotifications extends Component
{
    #[Computed]
    public function notifications(): mixed
    {
        return Notification::query()
            ->select(['id', 'recipient_user_id', 'user_id', 'title_translation_key', 'body_translation_key', 'data', 'translation_params_json', 'priority', 'created_at'])
            ->forUser(auth()->user())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.dashboard.notifications.guest-dashboard-notifications', [
            'notifications' => $this->notifications,
        ]);
    }
}
