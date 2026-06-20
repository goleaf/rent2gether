<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Services\Users\UserProfileVisibilityService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PublicHostProfileCard extends Component
{
    public int $userId;

    public function render(UserProfileVisibilityService $visibility): View
    {
        $host = User::query()->findOrFail($this->userId);

        return view('livewire.users.public-host-profile-card', [
            'profile' => $visibility->buildPublicHostProfile($host, $host),
        ]);
    }
}
