<?php

namespace App\Livewire\Favorites;

use App\Models\Favorite;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class FavoritesList extends Component
{
    #[Computed]
    public function favorites()
    {
        return Favorite::where('user_id', auth()->id())
            ->with(['bed.room.property'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function collections(): array
    {
        return Favorite::where('user_id', auth()->id())
            ->whereNotNull('collection')
            ->distinct()
            ->pluck('collection')
            ->toArray();
    }

    public function remove(int $favoriteId): void
    {
        Favorite::where('id', $favoriteId)->where('user_id', auth()->id())->delete();
        unset($this->favorites);
    }

    public function render(): View
    {
        return view('livewire.favorites.favorites-list');
    }
}
