<?php

namespace App\Livewire\Host;

use App\Actions\Account\StoreAvatarVariants;
use App\Livewire\Concerns\UsesAccountValidationAttributes;
use App\Livewire\Host\Concerns\ManagesHostProfileForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class HostOnboardingPage extends Component
{
    use ManagesHostProfileForm;
    use UsesAccountValidationAttributes;
    use WithFileUploads;

    public int $step = 1;

    public function mount(): void
    {
        $this->mountHostProfileForm();
    }

    public function nextStep(): void
    {
        $this->step = min(3, $this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function save(StoreAvatarVariants $avatars)
    {
        $validated = $this->validate($this->hostProfileRules(), attributes: $this->accountValidationAttributes());

        $this->persistHostProfile($validated, $avatars);
        session()->flash('success', __('notifications.flash.host_profile_updated'));

        return $this->redirect(route('host.profile.edit', ['locale' => app()->getLocale()]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.host.host-onboarding-page')
            ->layout('layouts.app', ['title' => __('host.profile.onboarding.title')]);
    }
}
