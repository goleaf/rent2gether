<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Services\UserProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditUserProfileForm extends Component
{
    #[Validate('nullable|string|max:80')]
    public string $displayName = '';

    #[Validate('nullable|string|max:80')]
    public string $publicName = '';

    #[Validate('nullable|string|max:255')]
    public string $publicCityName = '';

    public function mount(UserProfileService $profiles): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $profile = $profiles->getOrCreate($user);
            $this->displayName = (string) $profile->display_name;
            $this->publicName = (string) ($profile->public_name ?? '');
            $this->publicCityName = (string) ($profile->public_city_name ?? '');
        }
    }

    public function save(UserProfileService $profiles): void
    {
        $validated = $this->validate();
        $user = Auth::user();

        if ($user instanceof User) {
            $profiles->update($user, [
                'display_name' => $validated['displayName'],
                'public_name' => $validated['publicName'],
                'public_city_name' => $validated['publicCityName'],
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.profile.edit-user-profile-form');
    }
}
