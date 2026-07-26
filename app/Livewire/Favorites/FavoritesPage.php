<?php

namespace App\Livewire\Favorites;

use App\Models\Favorite;
use App\Models\User;
use App\Services\Favorites\FavoriteCardPresenter;
use App\Services\Favorites\FavoriteCardQuery;
use App\Services\Favorites\FavoriteChangeNotificationService;
use App\Services\Favorites\FavoriteCollectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class FavoritesPage extends Component
{
    public bool $createCollectionOpen = false;

    public function mount(FavoriteCollectionService $collections, FavoriteChangeNotificationService $notifications): void
    {
        $user = auth()->user();

        if ($user instanceof User) {
            $collections->ensureDefaultCollections($user);
            $notifications->notifyDueReminders($user);
        }
    }

    #[On('favorite-collections-changed')]
    #[On('favorite-removed')]
    public function refreshFavorites(): void
    {
        $this->createCollectionOpen = false;

        unset($this->summary, $this->recentCards, $this->priceChangedCards, $this->availableAgainCards, $this->reminderCards);
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function summary(): array
    {
        $query = Favorite::query()->forUser((int) auth()->id());

        return [
            'total' => (clone $query)->count(),
            'available' => (clone $query)->where('is_currently_available', true)->count(),
            'price_changed' => (clone $query)->where('price_changed', true)->count(),
            'available_again' => (clone $query)->where('became_available_again', true)->count(),
            'reminders' => (clone $query)->whereNotNull('remind_at')->whereNull('reminder_sent_at')->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function recentCards(): array
    {
        return $this->cards(
            $this->cardQuery()->recentlyAdded()->limit(6)
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function priceChangedCards(): array
    {
        return $this->cards(
            $this->cardQuery()->priceChanged()->recentlyAdded()->limit(4)
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function availableAgainCards(): array
    {
        return $this->cards(
            $this->cardQuery()->where('became_available_again', true)->recentlyAdded()->limit(4)
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function reminderCards(): array
    {
        return $this->cards(
            $this->cardQuery()->withReminder()->orderBy('remind_at')->limit(4)
        );
    }

    public function render(): View
    {
        return view('livewire.favorites.favorites-page');
    }

    private function cardQuery(): Builder
    {
        return app(FavoriteCardQuery::class)->forUser((int) auth()->id());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cards(Builder $query): array
    {
        return app(FavoriteCardPresenter::class)->presentMany($query->get());
    }
}
