<?php

namespace App\Livewire\Account;

use App\Livewire\Account\Concerns\ManagesGuestPreferenceForm;
use App\Livewire\Concerns\UsesAccountValidationAttributes;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestPreferenceEditPage extends Component
{
    use ManagesGuestPreferenceForm;
    use UsesAccountValidationAttributes;

    public function mount(): void
    {
        $this->mountPreferenceForm();
    }

    public function save(): void
    {
        $validated = $this->validate($this->preferenceRules(), attributes: $this->accountValidationAttributes());

        $this->persistPreferenceForm($validated);
        session()->flash('success', __('notifications.flash.guest_preferences_updated'));
    }

    public function render(): View
    {
        return view('livewire.account.guest-preference-edit-page')
            ->layout('layouts.app', ['title' => __('preferences.edit.title')]);
    }
}
