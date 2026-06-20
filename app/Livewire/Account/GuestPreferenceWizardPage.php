<?php

namespace App\Livewire\Account;

use App\Livewire\Account\Concerns\ManagesGuestPreferenceForm;
use App\Livewire\Concerns\UsesAccountValidationAttributes;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GuestPreferenceWizardPage extends Component
{
    use ManagesGuestPreferenceForm;
    use UsesAccountValidationAttributes;

    public int $step = 1;

    public function mount(): void
    {
        $this->mountPreferenceForm();
    }

    public function nextStep(): void
    {
        $this->step = min(4, $this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function save()
    {
        $validated = $this->validate($this->preferenceRules(), attributes: $this->accountValidationAttributes());

        $this->persistPreferenceForm($validated);
        session()->flash('success', __('notifications.flash.guest_preferences_updated'));

        return $this->redirect(route('profile.preferences.edit', ['locale' => app()->getLocale()]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.account.guest-preference-wizard-page')
            ->layout('layouts.app', ['title' => __('preferences.wizard.title')]);
    }
}
