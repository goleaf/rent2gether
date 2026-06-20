<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PublicUserCard extends Component
{
    public int $userId;

    public function render(): View
    {
        return view('livewire.users.public-user-card', [
            'user' => User::query()->select(['id', 'name', 'avatar_path'])->findOrFail($this->userId),
        ]);
    }
}
