<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Services\UserPrivacyService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PrivacySettingsForm extends Component
{
    public bool $showRealName = false;

    public bool $showCity = true;

    public bool $showLanguages = true;

    public bool $showPhoneAfterBooking = true;

    public function mount(UserPrivacyService $privacy): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $settings = $privacy->getOrCreate($user);
            $this->showRealName = (bool) $settings->show_real_name;
            $this->showCity = (bool) $settings->show_city;
            $this->showLanguages = (bool) $settings->show_languages;
            $this->showPhoneAfterBooking = (bool) $settings->show_phone_after_booking;
        }
    }

    public function save(UserPrivacyService $privacy): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $privacy->update($user, [
                'show_real_name' => $this->showRealName,
                'show_city' => $this->showCity,
                'show_languages' => $this->showLanguages,
                'show_phone_after_booking' => $this->showPhoneAfterBooking,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.profile.privacy-settings-form');
    }
}
