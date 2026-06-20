<?php

namespace App\Livewire\SavedSearches;

use App\Models\SavedSearch;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditSavedSearchSheet extends Component
{
    #[Locked]
    public int $savedSearchId;

    public string $title = '';

    public string $description = '';

    public string $budgetMax = '';

    public string $notificationFrequency = 'on_visit';

    public function mount(int $savedSearchId): void
    {
        $this->savedSearchId = $savedSearchId;
        $search = SavedSearch::query()->findOrFail($savedSearchId);
        $user = auth()->user();

        abort_unless($user instanceof User && (int) $search->user_id === (int) $user->id, 403);

        $this->title = $search->displayTitle();
        $this->description = (string) $search->description;
        $this->budgetMax = $search->budget_max === null ? '' : (string) $search->budget_max;
        $this->notificationFrequency = $search->notification_frequency ?: 'on_visit';
    }

    public function save(SavedSearchService $savedSearches): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $this->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'budgetMax' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'title' => __('saved_searches.search_name'),
            'description' => __('saved_searches.description'),
            'budgetMax' => __('saved_searches.budget'),
        ]);

        $savedSearches->update($user, SavedSearch::query()->findOrFail($this->savedSearchId), [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'budget_max' => $this->budgetMax !== '' ? (float) $this->budgetMax : null,
            'notification_frequency' => $this->notificationFrequency,
        ]);

        $this->dispatch('saved-search-updated');
    }

    public function render(): View
    {
        return view('livewire.saved-searches.edit-saved-search-sheet');
    }
}
