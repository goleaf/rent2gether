<?php

namespace App\Livewire\Favorites;

use App\Models\Favorite;
use App\Models\FavoriteCollection;
use App\Models\User;
use App\Services\Favorites\FavoriteService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MoveFavoriteSheet extends Component
{
    private const COLLECTION_LIMIT = 30;

    #[Locked]
    public int $favoriteId;

    public ?int $collectionId = null;

    public function mount(int $favoriteId): void
    {
        $favorite = Favorite::query()
            ->select(['id', 'user_id', 'favorite_collection_id'])
            ->where('user_id', auth()->id())
            ->findOrFail($favoriteId);

        $this->favoriteId = $favorite->id;
        $this->collectionId = $favorite->favorite_collection_id;
    }

    public function move(FavoriteService $favorites): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $this->validate([
            'collectionId' => [
                'required',
                'integer',
                Rule::exists('favorite_collections', 'id')
                    ->where(fn ($query) => $query
                        ->where('user_id', $user->id)
                        ->where('is_archived', 0)),
            ],
        ], attributes: [
            'collectionId' => __('favorites.collections'),
        ]);

        $favorites->moveToCollection($user, $this->favoriteId, $this->collectionId);
        $this->dispatch('favorite-collections-changed');
    }

    public function collections(): Collection
    {
        return FavoriteCollection::query()
            ->select(['id', 'user_id', 'title', 'type', 'is_default', 'is_archived', 'sort_order'])
            ->forUser((int) auth()->id())
            ->active()
            ->ordered()
            ->limit(self::COLLECTION_LIMIT)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.favorites.move-favorite-sheet', [
            'collections' => $this->collections(),
        ]);
    }
}
