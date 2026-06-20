<?php

namespace App\Livewire\Host\Profile;

use App\Models\User;
use App\Services\UserProfileVisibilityService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostPublicProfileCard extends Component
{
    public int $userId;

    public function render(UserProfileVisibilityService $visibility): View
    {
        $host = User::query()->findOrFail($this->userId);

        return view('livewire.host.profile.host-public-profile-card', [
            'profile' => $visibility->buildPublicHostProfile($host, $host),
        ]);
    }
}
