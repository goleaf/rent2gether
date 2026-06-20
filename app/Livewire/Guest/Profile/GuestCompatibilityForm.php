<?php

namespace App\Livewire\Guest\Profile;

use App\Models\User;
use App\Services\GuestCompatibilityProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GuestCompatibilityForm extends Component
{
    public bool $iLikeQuiet = false;

    public bool $iWorkRemotely = false;

    public bool $iNeedFastInternet = false;

    public bool $iAcceptLivingWithStrangers = true;

    public bool $iNeedLateEntry = false;

    public function save(GuestCompatibilityProfileService $profiles): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $profiles->update($user, [
                'i_like_quiet' => $this->iLikeQuiet,
                'i_work_remotely' => $this->iWorkRemotely,
                'i_need_fast_internet' => $this->iNeedFastInternet,
                'i_accept_living_with_strangers' => $this->iAcceptLivingWithStrangers,
                'i_need_late_entry' => $this->iNeedLateEntry,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.guest.profile.guest-compatibility-form');
    }
}
