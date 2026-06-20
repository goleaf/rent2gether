<?php

namespace App\Livewire\Guest\Profile;

use App\Models\User;
use App\Services\Users\GuestProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GuestProfileForm extends Component
{
    public bool $needsQuietPlace = false;

    public bool $needsFastWifi = false;

    public bool $acceptsSharedRoom = true;

    public function save(GuestProfileService $profiles): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $profiles->update($user, [
                'needs_quiet_place' => $this->needsQuietPlace,
                'needs_fast_wifi' => $this->needsFastWifi,
                'accepts_shared_room' => $this->acceptsSharedRoom,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.guest.profile.guest-profile-form');
    }
}
