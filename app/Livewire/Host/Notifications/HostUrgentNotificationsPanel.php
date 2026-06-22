<?php

namespace App\Livewire\Host\Notifications;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HostUrgentNotificationsPanel extends Component
{
    #[Computed]
    public function notifications(): mixed
    {
        abort_unless(auth()->check(), 403);

        return Notification::query()
            ->select([
                'id',
                'recipient_user_id',
                'user_id',
                'title_translation_key',
                'body_translation_key',
                'translation_params_json',
                'data',
                'priority',
                'is_urgent',
                'is_critical',
                'created_at',
            ])
            ->forUser(auth()->user())
            ->where(function ($query): void {
                $query->where('is_urgent', true)
                    ->orWhere('is_critical', true)
                    ->orWhereIn('priority', ['urgent', 'critical']);
            })
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.host.notifications.host-urgent-notifications-panel', [
            'notifications' => $this->notifications,
        ]);
    }
}
