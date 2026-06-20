<?php

namespace App\Livewire\Favorites;

use App\Models\Favorite;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class FavoriteTray extends Component
{
    #[On('favorite-collections-changed')]
    public function refreshTray(): void
    {
        unset($this->count);
    }

    #[Computed]
    public function count(): int
    {
        return Favorite::query()
            ->where('user_id', auth()->id() ?: 0)
            ->count();
    }

    public function render(): View
    {
        return view('livewire.favorites.favorite-tray');
    }
}
