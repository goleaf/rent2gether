<?php

namespace App\Livewire\Host\Profile;

use App\Models\User;
use App\Services\Users\HostProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class HostProfileForm extends Component
{
    #[Validate('nullable|string|max:100')]
    public string $hostDisplayName = '';

    public bool $publicPhoneVisible = false;

    public function save(HostProfileService $profiles): void
    {
        $this->validate();
        $user = Auth::user();

        if ($user instanceof User) {
            $profiles->update($user, [
                'host_display_name' => $this->hostDisplayName,
                'public_phone_visible' => $this->publicPhoneVisible,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.host.profile.host-profile-form');
    }
}
