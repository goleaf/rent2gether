<?php

namespace App\Livewire\Account;

use App\Livewire\Concerns\UsesAccountValidationAttributes;
use App\Models\UserSetting;
use App\Services\Privacy\PrivacyPreferences;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PrivacySettingsPage extends Component
{
    use UsesAccountValidationAttributes;

    public bool $showFullNameToConfirmedHostsOnly = true;

    public bool $showDisplayNamePublicly = true;

    public bool $showAge = false;

    public bool $showAgeRange = true;

    public bool $showCity = true;

    public bool $showLanguages = true;

    public bool $showOccupation = true;

    public bool $showAvatar = true;

    public bool $showReviews = true;

    public bool $showVerificationStatus = true;

    public bool $showPhoneAfterConfirmedBooking = true;

    public bool $showExactAddressBeforeBooking = false;

    public bool $showApproximateAreaBeforeBooking = true;

    public bool $showHostPhoneAfterConfirmedBooking = true;

    public bool $showCheckInInstructionsAfterConfirmation = true;

    public bool $hideSensitivePublicListingInfo = true;

    public function mount(): void
    {
        $settings = auth()->user()->setting()->firstOrCreate([], [
            'locale' => app()->getLocale(),
            'currency' => 'EUR',
            'active_mode' => UserSetting::MODE_GUEST,
            'account_role' => UserSetting::ROLE_GUEST,
            'privacy_preferences_json' => PrivacyPreferences::defaults(),
        ]);

        $preferences = PrivacyPreferences::normalize($settings->privacy_preferences_json);
        $guest = $preferences['guest'];
        $host = $preferences['host'];

        $this->showFullNameToConfirmedHostsOnly = $guest['show_full_name_to_confirmed_hosts_only'];
        $this->showDisplayNamePublicly = $guest['show_display_name_publicly'];
        $this->showAge = $guest['show_age'];
        $this->showAgeRange = $guest['show_age_range'];
        $this->showCity = $guest['show_city'];
        $this->showLanguages = $guest['show_languages'];
        $this->showOccupation = $guest['show_occupation'];
        $this->showAvatar = $guest['show_avatar'];
        $this->showReviews = $guest['show_reviews'];
        $this->showVerificationStatus = $guest['show_verification_status'];
        $this->showPhoneAfterConfirmedBooking = $guest['show_phone_after_confirmed_booking'];
        $this->showExactAddressBeforeBooking = $host['show_exact_address_before_booking'];
        $this->showApproximateAreaBeforeBooking = $host['show_approximate_area_before_booking'];
        $this->showHostPhoneAfterConfirmedBooking = $host['show_phone_after_confirmed_booking'];
        $this->showCheckInInstructionsAfterConfirmation = $host['show_checkin_instructions_after_confirmation'];
        $this->hideSensitivePublicListingInfo = $host['hide_sensitive_public_listing_info'];
    }

    public function save(): void
    {
        $validated = $this->validate([
            'showFullNameToConfirmedHostsOnly' => ['boolean'],
            'showDisplayNamePublicly' => ['boolean'],
            'showAge' => ['boolean'],
            'showAgeRange' => ['boolean'],
            'showCity' => ['boolean'],
            'showLanguages' => ['boolean'],
            'showOccupation' => ['boolean'],
            'showAvatar' => ['boolean'],
            'showReviews' => ['boolean'],
            'showVerificationStatus' => ['boolean'],
            'showPhoneAfterConfirmedBooking' => ['boolean'],
            'showExactAddressBeforeBooking' => ['boolean'],
            'showApproximateAreaBeforeBooking' => ['boolean'],
            'showHostPhoneAfterConfirmedBooking' => ['boolean'],
            'showCheckInInstructionsAfterConfirmation' => ['boolean'],
            'hideSensitivePublicListingInfo' => ['boolean'],
        ], attributes: $this->accountValidationAttributes());

        $preferences = PrivacyPreferences::normalize([
            'show_profile' => $validated['showDisplayNamePublicly'],
            'show_languages' => $validated['showLanguages'],
            'show_reviews' => $validated['showReviews'],
            'guest' => [
                'show_full_name_to_confirmed_hosts_only' => $validated['showFullNameToConfirmedHostsOnly'],
                'show_display_name_publicly' => $validated['showDisplayNamePublicly'],
                'show_age' => $validated['showAge'],
                'show_age_range' => $validated['showAgeRange'],
                'show_city' => $validated['showCity'],
                'show_languages' => $validated['showLanguages'],
                'show_occupation' => $validated['showOccupation'],
                'show_avatar' => $validated['showAvatar'],
                'show_reviews' => $validated['showReviews'],
                'show_verification_status' => $validated['showVerificationStatus'],
                'show_phone_after_confirmed_booking' => $validated['showPhoneAfterConfirmedBooking'],
            ],
            'host' => [
                'show_exact_address_before_booking' => $validated['showExactAddressBeforeBooking'],
                'show_approximate_area_before_booking' => $validated['showApproximateAreaBeforeBooking'],
                'show_phone_after_confirmed_booking' => $validated['showHostPhoneAfterConfirmedBooking'],
                'show_checkin_instructions_after_confirmation' => $validated['showCheckInInstructionsAfterConfirmation'],
                'hide_sensitive_public_listing_info' => $validated['hideSensitivePublicListingInfo'],
            ],
        ]);

        auth()->user()->setting()->updateOrCreate([], [
            'privacy_preferences_json' => $preferences,
        ]);

        session()->flash('success', __('notifications.flash.privacy_settings_updated'));
    }

    public function render(): View
    {
        return view('livewire.account.privacy-settings-page')
            ->layout('layouts.app', ['title' => __('account.privacy.title')]);
    }
}
