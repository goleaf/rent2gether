<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowProfile extends Component
{
    #[Locked]
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    #[Computed]
    public function reviewsAsGuest()
    {
        return $this->user->reviewsReceived()
            ->where('type', 'host_to_guest')
            ->published()
            ->with('reviewer')
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function reviewsAsHost()
    {
        return $this->user->reviewsReceived()
            ->where('type', 'guest_to_place')
            ->published()
            ->with('reviewer')
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.profile.show-profile');
    }
}
