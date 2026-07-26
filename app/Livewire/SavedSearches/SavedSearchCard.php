<?php

namespace App\Livewire\SavedSearches;

use App\Models\SavedSearch;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SavedSearchCard extends Component
{
    #[Locked]
    public int $searchId;

    /** @var array<string, mixed> */
    private array $card = [];

    /**
     * @param  array<string, mixed>  $card
     */
    public function mount(array $card): void
    {
        $this->card = $card;
        $this->searchId = (int) $card['id'];
    }

    public function runNow(SavedSearchService $savedSearches): void
    {
        $this->withSearch(fn (User $user, SavedSearch $search) => $savedSearches->runNow($user, $search));
        $this->dispatch('saved-search-updated');
    }

    public function pause(SavedSearchService $savedSearches): void
    {
        $this->withSearch(fn (User $user, SavedSearch $search) => $savedSearches->pause($user, $search));
        $this->dispatch('saved-search-updated');
    }

    public function resume(SavedSearchService $savedSearches): void
    {
        $this->withSearch(fn (User $user, SavedSearch $search) => $savedSearches->resume($user, $search));
        $this->dispatch('saved-search-updated');
    }

    public function archive(SavedSearchService $savedSearches): void
    {
        $this->withSearch(fn (User $user, SavedSearch $search) => $savedSearches->archive($user, $search));
        $this->dispatch('saved-search-updated');
    }

    public function render(): View
    {
        return view('livewire.saved-searches.saved-search-card', [
            'card' => $this->card,
        ]);
    }

    private function withSearch(callable $callback): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $search = SavedSearch::query()->findOrFail($this->searchId);

        $callback($user, $search);
    }
}
