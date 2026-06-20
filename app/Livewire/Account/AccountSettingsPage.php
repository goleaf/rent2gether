<?php

namespace App\Livewire\Account;

use App\Livewire\Concerns\UsesAccountValidationAttributes;
use App\Models\UserSetting;
use App\Services\Privacy\PrivacyPreferences;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AccountSettingsPage extends Component
{
    use UsesAccountValidationAttributes;

    public string $locale = 'en';

    public string $currency = 'EUR';

    public bool $emailMessages = true;

    public bool $emailBookings = true;

    public bool $productUpdates = false;

    public bool $showProfile = true;

    public bool $showLanguages = true;

    public bool $showReviews = true;

    public function mount(): void
    {
        $settings = auth()->user()->setting()->firstOrCreate([], [
            'locale' => app()->getLocale(),
            'currency' => 'EUR',
            'active_mode' => UserSetting::MODE_GUEST,
            'account_role' => UserSetting::ROLE_GUEST,
        ]);

        $notifications = $settings->notification_preferences_json ?: [];
        $privacy = PrivacyPreferences::normalize($settings->privacy_preferences_json);

        $this->locale = $settings->locale ?: app()->getLocale();
        $this->currency = $settings->currency ?: 'EUR';
        $this->emailMessages = (bool) ($notifications['email_messages'] ?? true);
        $this->emailBookings = (bool) ($notifications['email_bookings'] ?? true);
        $this->productUpdates = (bool) ($notifications['product_updates'] ?? false);
        $this->showProfile = (bool) $privacy['show_profile'];
        $this->showLanguages = (bool) $privacy['show_languages'];
        $this->showReviews = (bool) $privacy['show_reviews'];
    }

    public function save(): void
    {
        $validated = $this->validate([
            'locale' => ['required', Rule::in(config('localization.supported_locales'))],
            'currency' => ['required', Rule::in(['EUR', 'USD'])],
            'emailMessages' => ['boolean'],
            'emailBookings' => ['boolean'],
            'productUpdates' => ['boolean'],
            'showProfile' => ['boolean'],
            'showLanguages' => ['boolean'],
            'showReviews' => ['boolean'],
        ], attributes: $this->accountValidationAttributes());

        $privacy = PrivacyPreferences::normalize(auth()->user()->setting?->privacy_preferences_json);
        $privacy['show_profile'] = $validated['showProfile'];
        $privacy['show_languages'] = $validated['showLanguages'];
        $privacy['show_reviews'] = $validated['showReviews'];
        $privacy['guest']['show_display_name_publicly'] = $validated['showProfile'];
        $privacy['guest']['show_avatar'] = $validated['showProfile'];
        $privacy['guest']['show_languages'] = $validated['showLanguages'];
        $privacy['guest']['show_reviews'] = $validated['showReviews'];

        auth()->user()->setting()->updateOrCreate([], [
            'locale' => $validated['locale'],
            'currency' => $validated['currency'],
            'notification_preferences_json' => [
                'email_messages' => $validated['emailMessages'],
                'email_bookings' => $validated['emailBookings'],
                'product_updates' => $validated['productUpdates'],
            ],
            'privacy_preferences_json' => PrivacyPreferences::normalize($privacy),
        ]);

        app()->setLocale($validated['locale']);
        session()->put('locale', $validated['locale']);
        session()->flash('success', __('notifications.flash.account_settings_updated'));
    }

    public function render(): View
    {
        return view('livewire.account.account-settings-page')
            ->layout('layouts.app', ['title' => __('account.settings.title')]);
    }
}
