<?php

namespace App\Livewire\SavedSearches;

use App\Models\User;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateSavedSearchSheet extends Component
{
    public string $title = '';

    public string $description = '';

    public string $notificationFrequency = 'on_visit';

    public bool $notifyNewMatches = true;

    public bool $notifyPriceDrops = true;

    public bool $notifyAvailableAgain = true;

    public function save(SavedSearchService $savedSearches): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $this->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'title' => __('saved_searches.search_name'),
            'description' => __('saved_searches.description'),
        ]);

        $savedSearches->create($user, [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'notification_frequency' => $this->notificationFrequency,
            'notify_new_matches' => $this->notifyNewMatches,
            'notify_price_drops' => $this->notifyPriceDrops,
            'notify_available_again' => $this->notifyAvailableAgain,
        ]);

        $this->dispatch('saved-search-created');
    }

    public function render(): View
    {
        return view('livewire.saved-searches.create-saved-search-sheet');
    }
}
