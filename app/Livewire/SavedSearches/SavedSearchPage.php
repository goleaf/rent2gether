<?php

namespace App\Livewire\SavedSearches;

use App\Models\SavedSearch;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class SavedSearchPage extends Component
{
    #[Locked]
    public int $savedSearchId;

    public bool $editOpen = false;

    public bool $settingsOpen = false;

    public function mount(SavedSearch $savedSearch, SavedSearchService $savedSearches): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        abort_unless((int) $savedSearch->user_id === (int) $user->id, 403);

        $this->savedSearchId = $savedSearch->id;
        $savedSearches->runNow($user, $savedSearch);
    }

    #[On('saved-search-updated')]
    public function refreshSearch(): void
    {
        $this->editOpen = false;
        $this->settingsOpen = false;

        unset($this->search);
    }

    public function runNow(SavedSearchService $savedSearches): void
    {
        $this->withSearch(fn (User $user, SavedSearch $search) => $savedSearches->runNow($user, $search));
        unset($this->search);
    }

    public function pause(SavedSearchService $savedSearches): void
    {
        $this->withSearch(fn (User $user, SavedSearch $search) => $savedSearches->pause($user, $search));
        unset($this->search);
    }

    public function resume(SavedSearchService $savedSearches): void
    {
        $this->withSearch(fn (User $user, SavedSearch $search) => $savedSearches->resume($user, $search));
        unset($this->search);
    }

    public function archive(SavedSearchService $savedSearches): void
    {
        $this->withSearch(fn (User $user, SavedSearch $search) => $savedSearches->archive($user, $search));
        unset($this->search);
    }

    public function delete(SavedSearchService $savedSearches): void
    {
        $this->withSearch(fn (User $user, SavedSearch $search) => $savedSearches->delete($user, $search));

        $this->redirect(route('saved-searches.index', ['locale' => app()->getLocale()]), navigate: true);
    }

    #[Computed]
    public function search(): SavedSearch
    {
        return SavedSearch::query()
            ->select([
                'id',
                'user_id',
                'city_id',
                'title',
                'name',
                'description',
                'status',
                'city',
                'district',
                'check_in_date',
                'check_out_date',
                'nights_count',
                'calendar_days_count',
                'guests_count',
                'budget_min',
                'budget_max',
                'currency',
                'require_wifi',
                'require_locker',
                'only_instant_booking',
                'only_verified_hosts',
                'notification_frequency',
                'new_matches_count',
                'price_drops_count',
                'available_again_count',
                'last_checked_at',
                'is_active',
            ])
            ->with('cityModel:id,name')
            ->findOrFail($this->savedSearchId);
    }

    public function render(): View
    {
        return view('livewire.saved-searches.saved-search-page')
            ->layout('layouts.app', [
                'title' => $this->search->displayTitle(),
            ]);
    }

    private function withSearch(callable $callback): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $search = SavedSearch::query()->findOrFail($this->savedSearchId);

        $callback($user, $search);
    }
}
