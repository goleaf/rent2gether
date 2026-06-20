<?php

namespace App\Livewire\Favorites;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class FavoriteFilters extends Component
{
    public string $filter = 'all';

    public function updatedFilter(): void
    {
        $this->dispatch('favorite-filter-changed', filter: $this->filter);
    }

    public function render(): View
    {
        return view('livewire.favorites.favorite-filters');
    }
}
