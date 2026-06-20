<?php

namespace App\Livewire\Favorites;

use App\Data\Favorites\FavoriteContext;
use App\Models\Favorite;
use App\Models\User;
use App\Services\Favorites\FavoriteService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class FavoriteToggle extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public bool $selected = false;

    public ?int $collectionId = null;

    public string $source = 'unknown';

    public string $checkIn = '';

    public string $checkOut = '';

    public int $guestsCount = 1;

    public function mount(int $sleepingPlaceId, ?int $collectionId = null, string $source = 'unknown', string $checkIn = '', string $checkOut = '', int $guestsCount = 1): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
        $this->collectionId = $collectionId;
        $this->source = $source;
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
        $this->guestsCount = max(1, $guestsCount);

        $this->selected = auth()->check()
            && Favorite::query()
                ->where('user_id', auth()->id())
                ->where('sleeping_place_id', $sleepingPlaceId)
                ->exists();
    }

    public function toggle(FavoriteService $favorites): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            session()->put('intended_favorite', [
                'sleeping_place_id' => $this->sleepingPlaceId,
                'source' => $this->source,
            ]);

            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        $result = $favorites->toggle(
            user: $user,
            sleepingPlaceId: $this->sleepingPlaceId,
            context: new FavoriteContext(
                collectionId: $this->collectionId,
                source: $this->source,
                checkIn: $this->checkIn ?: null,
                checkOut: $this->checkOut ?: null,
                guestsCount: max(1, $this->guestsCount),
            ),
        );

        $this->selected = $result->selected;
        $this->dispatch('favorite-collections-changed');
    }

    public function render(): View
    {
        return view('livewire.favorites.favorite-toggle');
    }
}
