<?php

namespace App\Livewire\Favorites;

use App\Models\FavoriteCollection;
use App\Models\User;
use App\Services\Favorites\FavoriteService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class MoveFavoriteSheet extends Component
{
    public int $favoriteId;

    public ?int $collectionId = null;

    public function mount(int $favoriteId): void
    {
        $this->favoriteId = $favoriteId;
    }

    public function move(FavoriteService $favorites): void
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $this->collectionId) {
            return;
        }

        $favorites->moveToCollection($user, $this->favoriteId, $this->collectionId);
        $this->dispatch('favorite-collections-changed');
    }

    public function collections(): Collection
    {
        return FavoriteCollection::query()
            ->select(['id', 'user_id', 'title', 'is_archived', 'sort_order'])
            ->forUser((int) auth()->id())
            ->active()
            ->ordered()
            ->limit(40)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.favorites.move-favorite-sheet', [
            'collections' => $this->collections(),
        ]);
    }
}
