<?php

namespace App\Livewire\Host\Profile;

use App\Models\User;
use App\Services\HostRepresentativeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class HostRepresentativeForm extends Component
{
    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('nullable|string|max:40')]
    public string $phone = '';

    public bool $canHelpWithCheckIn = true;

    public function save(HostRepresentativeService $representatives): void
    {
        $this->validate();
        $user = Auth::user();

        if ($user instanceof User) {
            $representatives->create($user, [
                'name' => $this->name,
                'phone' => $this->phone,
                'can_help_with_check_in' => $this->canHelpWithCheckIn,
                'can_be_contacted_by_guest' => true,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.host.profile.host-representative-form');
    }
}
