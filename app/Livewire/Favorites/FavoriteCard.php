<?php

namespace App\Livewire\Favorites;

use App\Models\User;
use App\Services\Favorites\FavoriteCardPresenter;
use App\Services\Favorites\FavoriteCardQuery;
use App\Services\Favorites\FavoriteService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class FavoriteCard extends Component
{
    /** @var array<string, mixed> */
    private array $mountedCard = [];

    #[Locked]
    public int $favoriteId = 0;

    #[Locked]
    public int $placeId = 0;

    public bool $selectedForCompare = false;

    public bool $moveSheetOpen = false;

    public bool $noteSheetOpen = false;

    public bool $reminderSheetOpen = false;

    /**
     * @param  array<string, mixed>  $card
     */
    public function mount(array $card = [], bool $selectedForCompare = false): void
    {
        $this->mountedCard = $card;
        $this->favoriteId = (int) ($card['favorite_id'] ?? $card['id'] ?? 0);
        $this->placeId = (int) ($card['place_id'] ?? 0);
        $this->selectedForCompare = $selectedForCompare;
    }

    #[Renderless]
    public function remove(FavoriteService $favorites): void
    {
        $user = auth()->user();

        if (! $user instanceof User || $this->favoriteId <= 0) {
            return;
        }

        $favorites->removeFavorite($user, $this->favoriteId);

        $this->dispatch('favorite-removed');
    }

    public function openMoveSheet(FavoriteService $favorites): void
    {
        if ($this->canEdit($favorites)) {
            $this->moveSheetOpen = true;
            $this->noteSheetOpen = false;
            $this->reminderSheetOpen = false;
        }
    }

    public function openNoteSheet(FavoriteService $favorites): void
    {
        if ($this->canEdit($favorites)) {
            $this->noteSheetOpen = true;
            $this->moveSheetOpen = false;
            $this->reminderSheetOpen = false;
        }
    }

    public function openReminderSheet(FavoriteService $favorites): void
    {
        if ($this->canEdit($favorites)) {
            $this->reminderSheetOpen = true;
            $this->moveSheetOpen = false;
            $this->noteSheetOpen = false;
        }
    }

    public function setPriority(string $priority, FavoriteService $favorites): void
    {
        $user = auth()->user();

        if (! $user instanceof User || $this->favoriteId <= 0) {
            return;
        }

        $favorites->updatePriority($user, $this->favoriteId, $priority);
        $this->afterCardMutation();
    }

    public function setDecisionStatus(string $status, FavoriteService $favorites): void
    {
        $user = auth()->user();

        if (! $user instanceof User || $this->favoriteId <= 0) {
            return;
        }

        $favorites->updateDecisionStatus($user, $this->favoriteId, $status);
        $this->afterCardMutation();
    }

    public function toggleCompare(): void
    {
        $this->selectedForCompare = ! $this->selectedForCompare;
        $this->dispatch('favorite-compare-toggled', sleepingPlaceId: $this->placeId);
    }

    #[On('favorite-collections-changed')]
    public function closeSheets(): void
    {
        $this->moveSheetOpen = false;
        $this->noteSheetOpen = false;
        $this->reminderSheetOpen = false;

        unset($this->card);
    }

    public function render(): View
    {
        return view('livewire.favorites.favorite-card', [
            'card' => $this->card,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function card(): array
    {
        if ($this->mountedCard !== []) {
            return $this->mountedCard;
        }

        $user = auth()->user();

        if (! $user instanceof User || $this->favoriteId <= 0) {
            return [];
        }

        $favorite = app(FavoriteCardQuery::class)
            ->forFavorite($user, $this->favoriteId)
            ->first();

        if ($favorite === null) {
            return [];
        }

        return app(FavoriteCardPresenter::class)
            ->presentMany(collect([$favorite]))[0] ?? [];
    }

    private function canEdit(FavoriteService $favorites): bool
    {
        $user = auth()->user();

        if (! $user instanceof User || $this->favoriteId <= 0) {
            return false;
        }

        $favorites->favoriteForUser($user, $this->favoriteId);

        return true;
    }

    private function afterCardMutation(): void
    {
        $this->mountedCard = [];
        unset($this->card);

        $this->dispatch('favorite-collections-changed');
    }
}
