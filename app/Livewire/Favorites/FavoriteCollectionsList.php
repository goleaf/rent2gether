<?php

namespace App\Livewire\Favorites;

use App\Models\FavoriteCollection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class FavoriteCollectionsList extends Component
{
    #[On('favorite-collections-changed')]
    public function refreshCollections(): void
    {
        unset($this->collectionCards);
    }

    #[Computed]
    public function collectionCards(): Collection
    {
        return FavoriteCollection::query()
            ->select([
                'id',
                'user_id',
                'title',
                'description',
                'icon',
                'color',
                'type',
                'city_id',
                'check_in_date',
                'check_out_date',
                'is_default',
                'is_pinned',
                'is_archived',
                'sort_order',
                'updated_at',
            ])
            ->forUser((int) auth()->id())
            ->active()
            ->withCounts()
            ->ordered()
            ->limit(24)
            ->get()
            ->map(fn (FavoriteCollection $collection): array => [
                'id' => $collection->id,
                'title' => $collection->title,
                'description' => $collection->description,
                'icon' => $collection->icon ?: 'folder',
                'color' => $collection->color ?: 'zinc',
                'favorites_count' => (int) $collection->favorites_count,
                'available_count' => (int) $collection->available_favorites_count,
                'unavailable_count' => (int) $collection->unavailable_favorites_count,
                'price_changed_count' => (int) $collection->price_changed_favorites_count,
                'updated' => $collection->updated_at?->diffForHumans(),
                'url' => route('favorites.collections.show', [
                    'locale' => app()->getLocale(),
                    'favoriteCollection' => $collection,
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.favorites.favorite-collections-list');
    }
}
