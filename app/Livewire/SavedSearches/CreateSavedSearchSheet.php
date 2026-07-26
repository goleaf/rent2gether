<?php

namespace App\Livewire\SavedSearches;

use App\Livewire\SavedSearches\Concerns\ManagesSavedSearchForm;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateSavedSearchSheet extends Component
{
    use ManagesSavedSearchForm;

    public function save(SavedSearchService $savedSearches): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $savedSearches->create($user, $this->savedSearchPayload());

        $this->dispatch('saved-search-created');
    }

    public function render(): View
    {
        return view('livewire.saved-searches.create-saved-search-sheet');
    }
}
