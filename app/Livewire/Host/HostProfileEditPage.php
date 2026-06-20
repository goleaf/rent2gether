<?php

namespace App\Livewire\Host;

use App\Actions\Account\StoreAvatarVariants;
use App\Livewire\Concerns\UsesAccountValidationAttributes;
use App\Livewire\Host\Concerns\ManagesHostProfileForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class HostProfileEditPage extends Component
{
    use ManagesHostProfileForm;
    use UsesAccountValidationAttributes;
    use WithFileUploads;

    public function mount(): void
    {
        $this->mountHostProfileForm();
    }

    public function save(StoreAvatarVariants $avatars): void
    {
        $validated = $this->validate($this->hostProfileRules(), attributes: $this->accountValidationAttributes());

        $this->persistHostProfile($validated, $avatars);
        session()->flash('success', __('notifications.flash.host_profile_updated'));
    }

    public function render(): View
    {
        return view('livewire.host.host-profile-edit-page')
            ->layout('layouts.app', ['title' => __('host.profile.edit.title')]);
    }
}
