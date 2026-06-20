<?php

namespace App\Livewire\Favorites;

use App\Models\User;
use App\Services\Favorites\FavoriteService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FavoriteCard extends Component
{
    /** @var array<string, mixed> */
    public array $card = [];

    public bool $selectedForCompare = false;

    public function remove(FavoriteService $favorites): void
    {
        $user = auth()->user();

        if (! $user instanceof User || ! isset($this->card['favorite_id'])) {
            return;
        }

        $favorites->removeFavorite($user, (int) $this->card['favorite_id']);

        $this->dispatch('favorite-removed');
    }

    public function toggleCompare(): void
    {
        $this->selectedForCompare = ! $this->selectedForCompare;
        $this->dispatch('favorite-compare-toggled', sleepingPlaceId: (int) ($this->card['place_id'] ?? 0));
    }

    public function render(): View
    {
        return view('livewire.favorites.favorite-card');
    }
}
