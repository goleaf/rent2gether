<?php

namespace App\Livewire\Favorites;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class FavoriteSort extends Component
{
    public string $sort = 'recent';

    public function updatedSort(): void
    {
        $this->dispatch('favorite-sort-changed', sort: $this->sort);
    }

    public function render(): View
    {
        return view('livewire.favorites.favorite-sort');
    }
}
