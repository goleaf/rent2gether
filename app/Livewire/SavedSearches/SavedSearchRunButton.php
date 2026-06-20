<?php

namespace App\Livewire\SavedSearches;

use App\Models\SavedSearch;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SavedSearchRunButton extends Component
{
    #[Locked]
    public int $savedSearchId;

    public function mount(int $savedSearchId): void
    {
        $this->savedSearchId = $savedSearchId;
    }

    public function runNow(SavedSearchService $savedSearches): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $savedSearches->runNow($user, SavedSearch::query()->findOrFail($this->savedSearchId));
        $this->dispatch('saved-search-updated');
    }

    public function render(): View
    {
        return view('livewire.saved-searches.saved-search-run-button');
    }
}
