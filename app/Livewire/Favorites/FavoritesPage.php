<?php

namespace App\Livewire\Favorites;

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
        $user = User::query()
            ->select(['id'])
            ->withCount([
                'favorites as favorites_total_count',
                'favorites as favorites_available_count' => fn (Builder $query) => $query->where('is_currently_available', true),
                'favorites as favorites_price_changed_count' => fn (Builder $query) => $query->where('price_changed', true),
                'favorites as favorites_available_again_count' => fn (Builder $query) => $query->where('became_available_again', true),
                'favorites as favorites_reminders_count' => fn (Builder $query) => $query->whereNotNull('remind_at')->whereNull('reminder_sent_at'),
            ])
            ->find((int) auth()->id());

        if (! $user instanceof User) {
            return [
                'total' => 0,
                'available' => 0,
                'price_changed' => 0,
                'available_again' => 0,
                'reminders' => 0,
            ];
        }

        return [
            'total' => (int) $user->favorites_total_count,
            'available' => (int) $user->favorites_available_count,
            'price_changed' => (int) $user->favorites_price_changed_count,
            'available_again' => (int) $user->favorites_available_again_count,
            'reminders' => (int) $user->favorites_reminders_count,
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
