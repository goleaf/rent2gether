<?php

namespace App\Livewire\Notifications;

use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationSettingsPage extends Component
{
    public bool $inAppEnabled = true;

    public bool $emailEnabled = true;

    public bool $smsFutureEnabled = false;

    public bool $pushFutureEnabled = false;

    public bool $quietHoursEnabled = false;

    public ?string $quietHoursStart = null;

    public ?string $quietHoursEnd = null;

    public string $digestType = 'none';

    public string $languageLocale = 'en';

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);

        $preference = app(NotificationPreferenceService::class)->getOrCreateForUser(auth()->user());

        $this->inAppEnabled = $preference->in_app_enabled;
        $this->emailEnabled = $preference->email_enabled;
        $this->smsFutureEnabled = $preference->sms_future_enabled;
        $this->pushFutureEnabled = $preference->push_future_enabled;
        $this->quietHoursEnabled = $preference->quiet_hours_enabled;
        $this->quietHoursStart = $preference->quiet_hours_start;
        $this->quietHoursEnd = $preference->quiet_hours_end;
        $this->digestType = $preference->digest_type;
        $this->languageLocale = $preference->language_locale;
    }

    public function save(): void
    {
        app(NotificationPreferenceService::class)->updatePreferences(auth()->user(), [
            'in_app_enabled' => $this->inAppEnabled,
            'email_enabled' => $this->emailEnabled,
            'sms_future_enabled' => $this->smsFutureEnabled,
            'push_future_enabled' => $this->pushFutureEnabled,
            'quiet_hours_enabled' => $this->quietHoursEnabled,
            'quiet_hours_start' => $this->quietHoursStart,
            'quiet_hours_end' => $this->quietHoursEnd,
            'digest_type' => $this->digestType,
            'language_locale' => $this->languageLocale,
        ]);
    }

    public function render(): View
    {
        return view('livewire.notifications.notification-settings-page')
            ->layout('layouts.app', ['title' => __('notifications.settings.title')]);
    }
}
