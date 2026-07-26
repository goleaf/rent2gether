<?php

namespace App\Livewire\SavedSearches;

use App\Livewire\SavedSearches\Concerns\ManagesSavedSearchForm;
use App\Models\SavedSearch;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditSavedSearchSheet extends Component
{
    use ManagesSavedSearchForm;

    #[Locked]
    public int $savedSearchId;

    public function mount(int $savedSearchId): void
    {
        $this->savedSearchId = $savedSearchId;
        $search = SavedSearch::query()->with('cityModel:id,name')->findOrFail($savedSearchId);
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $search->user_id === (int) $user->id, 403);

        $this->loadSavedSearchForm($search);
    }

    public function save(SavedSearchService $savedSearches): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $savedSearches->update($user, SavedSearch::query()->findOrFail($this->savedSearchId), $this->savedSearchPayload());

        $this->dispatch('saved-search-updated');
    }

    public function render(): View
    {
        return view('livewire.saved-searches.edit-saved-search-sheet');
    }
}
