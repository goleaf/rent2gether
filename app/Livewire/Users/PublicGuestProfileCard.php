<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Services\UserProfileVisibilityService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PublicGuestProfileCard extends Component
{
    public int $userId;

    public function render(UserProfileVisibilityService $visibility): View
    {
        $guest = User::query()->findOrFail($this->userId);

        return view('livewire.users.public-guest-profile-card', [
            'profile' => $visibility->buildPublicGuestProfile($guest, $guest),
        ]);
    }
}
